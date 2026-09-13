<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{
    doc: DocumentSchema;
}>();

const formatDate = (iso?: string) => {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};
</script>

<template>
    <div class="h-full w-full bg-white p-12 text-gray-800 flex flex-col" :style="{ fontFamily: doc.style.fontFamily }">
        <h1
            class="text-2xl font-bold text-center uppercase tracking-wide mb-10 pb-4 border-b-2"
            :style="{ color: doc.style.primaryColor, borderColor: doc.style.primaryColor }"
        >
            {{ doc.type === 'certificate' ? 'Certificat' : 'Attestation' }}
        </h1>

        <p v-if="doc.certificate?.purpose" class="text-center text-sm font-medium text-gray-500 mb-8 uppercase">
            {{ doc.certificate.purpose }}
        </p>

        <p class="text-center font-semibold text-lg mb-6">{{ doc.certificate?.recipientName || 'Nom du bénéficiaire' }}</p>

        <div class="flex-1 text-sm leading-relaxed whitespace-pre-line text-justify">
            {{ doc.certificate?.body || '' }}
        </div>

        <div class="flex justify-between items-end mt-10 pt-6 border-t text-sm">
            <p class="text-gray-500">Fait le {{ formatDate(doc.certificate?.issueDate) || '____________' }}</p>
            <div class="text-right">
                <p class="font-semibold">{{ doc.certificate?.issuerName || 'Nom du signataire' }}</p>
                <p class="text-gray-500 text-xs">{{ doc.certificate?.issuerTitle }}</p>
            </div>
        </div>
    </div>
</template>
