import { Card } from '@/Components/ui/card';
import { ImageUploadContainer } from './ImageUploadContainer';

export function ImageUploadFeature({
    transactions,
}: {
    transactions: Transaction[];
}) {
    return (
        <div className="w-full p-6">
            <div className="mx-auto grid max-w-[1200px] grid-cols-[1fr_400px] gap-6">
                <TransactionsTable transactions={transactions} />
                <ImageUploadContainer />
            </div>
        </div>
    );
}

export type Transaction = {
    date: string;
    description: string;
    amount: string;
};

function TransactionsTable({ transactions }: { transactions: Transaction[] }) {
    return (
        <Card className="bg-white p-4">
            <div className="relative overflow-x-auto">
                {transactions.length > 0 && (
                    <table className="w-full text-left text-sm">
                        <thead className="bg-gray-50 text-xs uppercase dark:bg-gray-700">
                            <tr>
                                <th scope="col" className="px-6 py-3">
                                    Data
                                </th>
                                <th scope="col" className="px-6 py-3">
                                    Descrição
                                </th>
                                <th scope="col" className="px-6 py-3">
                                    Valor
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {transactions.map((transaction, index) => (
                                <tr
                                    key={index}
                                    className="border-b bg-white dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <td className="px-6 py-4">
                                        {transaction.date}
                                    </td>
                                    <td className="px-6 py-4">
                                        {transaction.description}
                                    </td>
                                    <td className="px-6 py-4">
                                        {transaction.amount}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </Card>
    );
}
