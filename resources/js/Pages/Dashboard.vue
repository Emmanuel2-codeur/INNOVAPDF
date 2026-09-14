<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { dashboard } from '@/routes';
import { Plus, Search, Trash2, FileText, FolderOpen, ExternalLink } from '@lucide/vue';

const GUEST_DRAFT_KEY = 'innovapdf_pending_document';
const isRecoveringDraft = ref(false);

onMounted(() => {
    const draft = sessionStorage.getItem(GUEST_DRAFT_KEY);
    if (!draft) return;

    isRecoveringDraft.value = true;
    sessionStorage.removeItem(GUEST_DRAFT_KEY);

    router.post('/documents', JSON.parse(draft), {
        onFinish: () => { isRecoveringDraft.value = false; },
    });
});

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
            { title: 'Dashboard', href: dashboard() },
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

const typeColors: Record<string, string> = {
    cv: 'bg-emerald-500/15 text-emerald-400',
    invoice: 'bg-sky-500/15 text-sky-400',
    cover_letter: 'bg-amber-500/15 text-amber-400',
    quote: 'bg-violet-500/15 text-violet-400',
    certificate: 'bg-rose-500/15 text-rose-400',
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

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 bg-[var(--innova-bg)] text-[var(--innova-text)] min-h-full">
        <div
            v-if="isRecoveringDraft"
            class="rounded-xl bg-[var(--innova-surface-2)] border border-[var(--innova-border)] text-[var(--innova-green-light)] text-sm px-4 py-3"
        >
            Enregistrement de ton document en cours…
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Mes documents</h1>
                <p class="text-sm text-[var(--innova-text-muted)] mt-0.5">Gère, modifie et exporte tes créations INNOVAPDF.</p>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    href="/trash"
                    class="inline-flex items-center gap-1.5 text-sm text-[var(--innova-text-muted)] hover:text-[var(--innova-text)] transition px-3 py-2"
                >
                    <Trash2 class="size-4" />
                    Corbeille
                </Link>
                <Link
                    href="/editor/new"
                    class="inline-flex items-center gap-2 bg-[var(--innova-green)] hover:brightness-110 text-black px-5 py-2.5 rounded-full text-sm font-semibold transition shadow-[0_0_24px_-4px_rgba(34,197,94,0.6)]"
                >
                    <Plus class="size-4" />
                    Nouveau document
                </Link>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <Search class="size-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--innova-text-muted)]" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher par titre..."
                    class="w-full text-sm rounded-full bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] placeholder:text-[var(--innova-text-muted)] pl-10 pr-4 py-2.5 focus:outline-none focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] transition"
                />
            </div>
            <select
                v-model="type"
                class="text-sm rounded-full bg-[var(--innova-surface)] border border-[var(--innova-border)] text-[var(--innova-text)] px-4 py-2.5 focus:outline-none focus:border-[var(--innova-green)] focus:ring-1 focus:ring-[var(--innova-green)] transition"
            >
                <option value="">Tous les types</option>
                <option value="cv">CV</option>
                <option value="invoice">Facture</option>
                <option value="cover_letter">Lettre de motivation</option>
                <option value="quote">Devis</option>
                <option value="certificate">Attestation</option>
            </select>
        </div>

        <div
            v-if="documents.length === 0"
            class="border border-dashed border-[var(--innova-border)] rounded-2xl p-14 text-center bg-[var(--innova-surface)]/40"
        >
            <FolderOpen class="size-10 mx-auto mb-3 text-[var(--innova-text-muted)]" />
            <p v-if="search || type" class="text-[var(--innova-text-muted)]">Aucun document ne correspond à ta recherche.</p>
            <p v-else class="text-[var(--innova-text-muted)]">
                Tu n'as pas encore de document.
                <Link href="/editor/new" class="text-[var(--innova-green)] hover:underline font-medium">Crée le premier</Link>
                pour commencer.
            </p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="doc in documents"
                :key="doc.id"
                class="group relative rounded-2xl bg-[var(--innova-surface)] border border-[var(--innova-border)] p-5 hover:border-[var(--innova-green)]/50 hover:shadow-[0_0_30px_-10px_rgba(34,197,94,0.35)] transition"
            >
                <div class="flex items-start justify-between mb-4">
                    <span
                        class="text-[11px] font-medium px-2.5 py-1 rounded-full"
                        :class="typeColors[doc.type] ?? 'bg-[var(--innova-surface-2)] text-[var(--innova-text-muted)]'"
                    >
                        {{ typeLabels[doc.type] ?? doc.type }}
                    </span>
                    <span v-if="doc.status === 'draft'" class="text-[11px] text-amber-400/90">Brouillon</span>
                </div>

                <Link :href="`/editor/${doc.id}`" class="block">
                    <div class="flex items-center gap-2 mb-2">
                        <FileText class="size-4 text-[var(--innova-text-muted)] shrink-0" />
                        <p class="font-semibold text-[var(--innova-text)] truncate">{{ doc.title || 'Sans titre' }}</p>
                    </div>
                    <p class="text-xs text-[var(--innova-text-muted)]">Modifié le {{ formatDate(doc.updated_at) }}</p>
                </Link>

                <div class="flex items-center gap-4 mt-4 pt-4 border-t border-[var(--innova-border)]">
                    <Link
                        :href="`/editor/${doc.id}`"
                        class="inline-flex items-center gap-1 text-xs text-[var(--innova-green)] hover:underline font-medium"
                    >
                        <ExternalLink class="size-3.5" />
                        Ouvrir
                    </Link>
                    <button
                        @click="confirmDelete(doc)"
                        class="inline-flex items-center gap-1 text-xs text-red-400/90 hover:text-red-400 hover:underline font-medium ml-auto"
                    >
                        <Trash2 class="size-3.5" />
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>