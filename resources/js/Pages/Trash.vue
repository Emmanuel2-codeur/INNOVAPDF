<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

interface TrashedDocument {
    id: number;
    title: string;
    type: string;
    template: string;
    status: string;
    deleted_at: string;
}

defineProps<{
    documents: TrashedDocument[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Corbeille', href: '/trash' },
        ],
    },
});

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const restore = (doc: TrashedDocument) => {
    router.patch(`/documents/${doc.id}/restore`, {}, { preserveScroll: true });
};

const forceDelete = (doc: TrashedDocument) => {
    if (!confirm(`Supprimer définitivement "${doc.title}" ? Cette action est IRRÉVERSIBLE, le document sera perdu pour toujours.`)) return;
    router.delete(`/documents/${doc.id}/force`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Corbeille" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-800">Corbeille</h1>
            <Link href="/dashboard" class="text-sm text-indigo-600 hover:underline">← Retour aux documents</Link>
        </div>

        <p class="text-sm text-gray-500">
            Les documents supprimés restent ici jusqu'à leur restauration ou leur suppression définitive.
        </p>

        <div v-if="documents.length === 0" class="border rounded-xl p-12 text-center text-gray-400">
            La corbeille est vide.
        </div>

        <div v-else class="border rounded-xl divide-y bg-white overflow-hidden">
            <div
                v-for="doc in documents"
                :key="doc.id"
                class="flex items-center justify-between px-4 py-3"
            >
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ doc.title || 'Sans titre' }}</p>
                    <p class="text-xs text-gray-500">Supprimé le {{ formatDate(doc.deleted_at) }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0 ml-4">
                    <button @click="restore(doc)" class="text-xs text-green-600 hover:underline font-medium">
                        Restaurer
                    </button>
                    <button @click="forceDelete(doc)" class="text-xs text-red-600 hover:underline font-medium">
                        Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>