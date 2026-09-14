<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';
import { Award } from 'lucide-vue-next';

defineProps<{ doc: DocumentSchema }>();

const formatDate = (iso?: string) => {
    if (!iso) return '____________';
    return new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 relative overflow-hidden flex flex-col items-center justify-center px-16 py-10"
         :style="{ fontFamily: doc.style.fontFamily }">
        <div class="absolute top-0 left-0 w-40 h-16 bg-[#111]" style="clip-path: polygon(0 0, 100% 0, 60% 100%, 0 100%);"></div>
        <div class="absolute bottom-0 right-0 w-40 h-16 bg-[#111]" style="clip-path: polygon(40% 0, 100% 0, 100% 100%, 0 100%);"></div>
        <div class="absolute top-0 left-0 w-40 h-3" :style="{ backgroundColor: doc.style.primaryColor }"></div>
        <div class="absolute bottom-0 right-0 w-40 h-3" :style="{ backgroundColor: doc.style.primaryColor }"></div>

        <div class="absolute top-8 right-10 w-14 h-14 rounded-full flex items-center justify-center" :style="{ backgroundColor: doc.style.primaryColor }">
            <Award class="w-7 h-7 text-white" />
        </div>

        <h1 class="text-3xl font-bold uppercase tracking-[0.15em] mb-1 relative z-10">{{ doc.type === 'certificate' ? 'Certificate' : 'Attestation' }}</h1>
        <p class="text-xs uppercase tracking-[0.3em] text-gray-400 mb-8">{{ doc.certificate?.purpose || 'of appreciation' }}</p>

        <p class="text-xs text-gray-500 mb-2">Proudly presented to</p>
        <p class="text-2xl italic font-semibold mb-8 border-b pb-2" :style="{ borderColor: doc.style.primaryColor, fontFamily: 'Georgia, serif' }">
            {{ doc.certificate?.recipientName || 'Nom du bénéficiaire' }}
        </p>

        <p class="text-xs text-gray-500 max-w-md text-center mb-10 whitespace-pre-line">{{ doc.certificate?.body || '' }}</p>

        <div class="flex justify-between w-full max-w-md text-xs text-gray-500 relative z-10">
            <div class="text-center"><p class="border-t border-gray-300 pt-1 w-32">{{ formatDate(doc.certificate?.issueDate) }}</p><p class="text-[9px]">Date</p></div>
            <div class="text-center"><p class="border-t border-gray-300 pt-1 w-32 font-semibold">{{ doc.certificate?.issuerName || '' }}</p><p class="text-[9px]">{{ doc.certificate?.issuerTitle || 'Signature' }}</p></div>
        </div>
    </div>
</template>
