import { ImagePlus, Send, X } from 'lucide-react';

import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Loader } from 'lucide-react';
export function ImageUploadContainer() {
    const [selectedImage, setSelectedImage] = useState<string | null>(null);
    const [file, setFile] = useState<File | null>(null);
    const [loading, setLoading] = useState(false);

    const handleImageSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            setFile(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setSelectedImage(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleRemoveImage = () => {
        setSelectedImage(null);
    };

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!file) {
            return;
        }

        router.post(
            '/upload',
            {
                file,
            },
            {
                forceFormData: true,
                fresh: true,
                onFinish() {
                    setLoading(false);
                },
            },
        );

        setLoading(true);
        setFile(null);
        setSelectedImage(null);
    };

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            {loading && (
                <div className="absolute right-6 top-6">
                    <Loader className="h-6 w-6 animate-spin" />
                </div>
            )}
            {selectedImage && (
                <div className="flex justify-end gap-2">
                    <Button
                        variant="secondary"
                        size="sm"
                        onClick={handleRemoveImage}
                    >
                        <X className="mr-2 h-4 w-4" />
                        Remover Imagem
                    </Button>
                    <Button size="sm" type="submit">
                        <Send className="mr-2 h-4 w-4" />
                        Enviar Imagem
                    </Button>
                </div>
            )}
            <Card className="flex min-h-[400px] flex-col items-center justify-center bg-white p-4">
                {selectedImage ? (
                    <img
                        src={selectedImage}
                        alt="Selected"
                        className="h-full w-full rounded-md object-cover"
                    />
                ) : (
                    <label className="flex h-full w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <div className="flex flex-col items-center justify-center pb-6 pt-5">
                            <ImagePlus className="mb-4 h-12 w-12 text-gray-400" />
                            <p className="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                <span className="font-semibold">
                                    Selecionar Imagem
                                </span>
                            </p>
                        </div>
                        <input
                            type="file"
                            className="hidden"
                            accept="image/*"
                            onChange={handleImageSelect}
                        />
                    </label>
                )}
            </Card>
        </form>
    );
}
