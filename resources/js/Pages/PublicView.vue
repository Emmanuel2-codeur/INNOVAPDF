<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MinimalCV from '@/components/Templates/CV/Minimal.vue';
import ModernCV from '@/components/Templates/CV/Modern.vue';
import ClassicInvoice from '@/components/Templates/Invoice/Classic.vue';
import CoverLetterDefault from '@/components/Templates/CoverLetter/Default.vue';
import QuoteSimple from '@/components/Templates/Quote/Simple.vue';
import CertificateDefault from '@/components/Templates/Certificate/Default.vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{
    document: DocumentSchema & { content: Record<string, unknown> };
    token: string;
}>();

const doc = {
    ...props.document,
    profile: props.document.content?.profile,
    experiences: props.document.content?.experiences,
    invoiceItems: props.document.content?.invoiceItems,
    invoiceMeta: props.document.content?.invoiceMeta,
    coverLetter: props.document.content?.coverLetter,
    certificate: props.document.content?.certificate,
} as DocumentSchema;
</script>

<template>
    <Head :title="document.title" />

    <div class="min-h-screen bg-gray-100 flex flex-col items-center py-10 px-4">
        <div class="w-full max-w-3xl mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Document partagé via INNOVAPDF</p>
            <a :href="`/share/${props.token}/pdf`" class="text-sm text-indigo-600 hover:underline font-medium">
                Télécharger en PDF
            </a>
        </div>

        <div class="w-full max-w-3xl aspect-[210/297] bg-white shadow-lg overflow-hidden">
            <MinimalCV v-if="doc.type === 'cv' && doc.template === 'minimal'" :doc="doc" />
            <ModernCV v-if="doc.type === 'cv' && doc.template === 'modern'" :doc="doc" />
            <CoverLetterDefault v-if="doc.type === 'cover_letter'" :doc="doc" />
            <ClassicInvoice v-if="doc.type === 'invoice'" :doc="doc" />
            <QuoteSimple v-if="doc.type === 'quote'" :doc="doc" />
            <CertificateDefault v-if="['attestation', 'certificate'].includes(doc.type)" :doc="doc" />
        </div>
    </div>
</template>
