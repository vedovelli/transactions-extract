<?php

use App\Http\Controllers\ProfileController;
use App\Models\Transaction;
use EchoLabs\Prism\Schema\ArraySchema;
use EchoLabs\Prism\Schema\NumberSchema;
use EchoLabs\Prism\ValueObjects\Messages\Support\Image;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Schema\ObjectSchema;
use EchoLabs\Prism\Schema\StringSchema;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    Number::useCurrency('BRL');
    Number::useLocale('pt-BR');

    $transactions = Transaction::all()->map(function ($transaction) {
        return [
            ...$transaction->toArray(),
            'description' => Str::limit($transaction->description, 50),
            'amount' => Number::currency($transaction->amount/100),
        ];
    });

    return Inertia::render('Dashboard', compact('transactions'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('upload', function (Request $request) {
    defer(function () use ($request) {
        $file = $request->file('file');
        $path = $file->storeAs('transactions', $file->getClientOriginalName(), 's3');
        $imageUrl = env('AWS_CDN_URL').'/'.$path;

        $schema = new ObjectSchema(
            name: 'extracted_transactions',
            description: "User's transactions from a screengrab",
            properties: [
                new ArraySchema(
                    name: 'transactions',
                    description: "User's transactions from a screengrab",
                    items: new ObjectSchema(
                        name: 'transaction',
                        description: 'A single transaction',
                        properties: [
                            new StringSchema('date', 'The transaction date in the format yyyy-mm-dd hh:ii:ss'),
                            new StringSchema('description', 'The transaction description'),
                            new NumberSchema('amount', 'The transaction amount in cents'),
                        ],
                        requiredFields: ['date', 'description', 'amount']
                    )
                ),
            ],
            requiredFields: ['name', 'properties']
        );

        $message = new UserMessage(
            "You're an agent specialized in identifying financial transaction on provided images. Extract financial transactions from the following screen grab:",
            [
                Image::fromUrl(
                    $imageUrl,
                )
            ]
        );

        $response = Prism::structured()
            ->using(Provider::OpenAI, 'gpt-4o-mini')
            ->withSchema($schema)
            ->withMessages([$message])
            ->generate();

        collect($response->structured['transactions'])->each(function ($transaction)  {
            Transaction::create($transaction);
        });
    });

    return back();
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
