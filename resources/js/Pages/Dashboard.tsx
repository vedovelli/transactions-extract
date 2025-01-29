import {
    ImageUploadFeature,
    Transaction,
} from '@/Components/ImageUploader/ImageUploadFeature';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

export default function Dashboard({
    transactions,
}: PageProps<{ transactions: Transaction[] }>) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />
            <ImageUploadFeature transactions={transactions} />
        </AuthenticatedLayout>
    );
}
