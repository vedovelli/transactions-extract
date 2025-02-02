<?php

use App\Http\Controllers\ProfileController;
use App\Models\Transaction;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Schema\ArraySchema;
use EchoLabs\Prism\Schema\NumberSchema;
use EchoLabs\Prism\Schema\ObjectSchema;
use EchoLabs\Prism\Schema\StringSchema;
use EchoLabs\Prism\ValueObjects\Messages\Support\Image;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

function seedTransactions(): void
{
    Transaction::truncate();
    Transaction::factory()->count(10)->create();
}

function getTransactions()
{
    return Transaction::all()->map(function ($transaction) {
        return [
            ...$transaction->toArray(),
            'description' => Str::limit($transaction->description, 50),
            'amount' => Number::currency($transaction->amount / 100),
            'date' => $transaction->created_at->format('d/m/Y'),
        ];
    });
}

Route::get('/dashboard', function () {
    //    seedTransactions();

    return Inertia::render('Dashboard', ['transactions' => getTransactions()]);

})->middleware(['auth', 'verified'])->name('dashboard');



Route::post('upload', function (Request $request) {
    try {
        $file = $request->file(key: 'file');

        $path = $file->storeAs('screencast/' . now()->timestamp, $file->getClientOriginalName(), 's3');

        $fileUrl = config('app.cdn_url') . "/$path";

        $schema = new ObjectSchema(
            name: 'transactions_from_image',
            description: 'A list of transactions from a screenshot',
            properties: [
                new ArraySchema(
                    name: 'transactions',
                    description: "User's transactions from a screengrab",
                    items: new ObjectSchema(
                        name: 'transaction',
                        description: 'A single transaction',
                        properties: [
                            new StringSchema('description', 'The transaction description'),
                            new NumberSchema('amount', 'The transaction amount in cents'),
                        ],
                        requiredFields: ['description', 'amount']
                    )
                )
            ],
            requiredFields: ['properties']
        );

        $userMessage = new UserMessage(
            "You're an agent specialized in identifying financial transaction on provided images. Extract financial transactions from the following screenshot:",
            [
                Image::fromUrl($fileUrl)
            ]
        );

        $response = Prism::structured()
            ->using(Provider::OpenAI, 'gpt-4o-mini')
            ->withSchema($schema)
            ->withMessages([$userMessage])
            ->generate();

        $structured = $response->structured;

        collect($structured['transactions'])->each(function ($transaction) {
            Transaction::create([
                ...$transaction,
                'date' => now()
            ]);
        });

    } catch (Exception $exception) {
        Log::error($exception->getMessage());
    }

    return back();
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
