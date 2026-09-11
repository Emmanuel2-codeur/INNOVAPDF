<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { dashboard } from '@/routes';

interface DocumentRow {
    id: number;
    title: string;
    type: string;
    template: string;
    status: string;
    updated_at: string;
}

const props = defineProps<{
    documents: DocumentRow[];
    filters: { search?: string; type?: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/dashboard',
        { search: search.value || undefined, type: type.value || undefined },
        { preserveState: true, replace: true }
    );
};

// Recherche avec debounce pour ne pas déclencher une requête à chaque frappe.
watch(search, () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});

watch(type, applyFilters);

const typeLabels: Record<string, string> = {
    cv: 'CV',
    invoice: 'Facture',
    cover_letter: 'Lettre de motivation',
    quote: 'Devis',
    certificate: 'Attestation',
};

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const confirmDelete = (doc: DocumentRow) => {
    if (!confirm(`Supprimer "${doc.title}" ? Cette action est irréversible.`)) return;
    router.delete(`/documents/${doc.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-bold text-gray-800">Mes documents</h1>
            <div class="flex items-center gap-3">
                <Link href="/trash" class="text-sm text-gray-500 hover:underline">Corbeille</Link>
                <Link
                    href="/editor/new"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition"
                >
                    + Nouveau document
                </Link>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher par titre..."
                class="flex-1 min-w-200px text-sm rounded-md border-gray-300"
            />
            <select v-model="type" class="text-sm rounded-md border-gray-300">
                <option value="">Tous les types</option>
                <option value="cv">CV</option>
                <option value="invoice">Facture</option>
                <option value="cover_letter">Lettre de motivation</option>
                <option value="quote">Devis</option>
                <option value="certificate">Attestation</option>
            </select>
        </div>

        <div v-if="documents.length === 0" class="border rounded-xl p-12 text-center text-gray-400">
            <p v-if="search || type">Aucun document ne correspond à ta recherche.</p>
            <p v-else>Tu n'as pas encore de document. Clique sur "Nouveau document" pour commencer.</p>
        </div>

        <div v-else class="border rounded-xl divide-y bg-white overflow-hidden">
            <div
                v-for="doc in documents"
                :key="doc.id"
                class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition"
            >
                <Link :href="`/editor/${doc.id}`" class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ doc.title || 'Sans titre' }}</p>
                    <p class="text-xs text-gray-500">
                        {{ typeLabels[doc.type] ?? doc.type }} · Modifié le {{ formatDate(doc.updated_at) }}
                        <span v-if="doc.status === 'draft'" class="ml-1 text-amber-600">· Brouillon</span>
                    </p>
                </Link>
                <div class="flex items-center gap-2 shrink-0 ml-4">
                    <Link :href="`/editor/${doc.id}`" class="text-xs text-indigo-600 hover:underline">Ouvrir</Link>
                    <button @click="confirmDelete(doc)" class="text-xs text-red-500 hover:underline">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</template>