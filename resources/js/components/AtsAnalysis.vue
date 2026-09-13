<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{
    doc: DocumentSchema;
}>();

const jobOffer = ref('');
const isLoading = ref(false);
const error = ref<string | null>(null);
const result = ref<string | null>(null);

const buildCvText = () => {
    const p = props.doc.profile;
    const lines = [
        `${p?.fullName ?? ''} - ${p?.title ?? ''}`,
        p?.summary ?? '',
        ...(props.doc.experiences ?? []).map(e => `${e.position} chez ${e.company} : ${e.description}`),
    ];
    return lines.filter(Boolean).join('\n');
};

const analyze = async () => {
    if (!jobOffer.value.trim()) {
        error.value = "Colle d'abord l'offre d'emploi.";
        return;
    }

    isLoading.value = true;
    error.value = null;
    result.value = null;

    try {
        const { data } = await axios.post('/ai/ats', {
            cv_text: buildCvText(),
            job_offer: jobOffer.value,
        });
        result.value = data.result;
    } catch (e: any) {
        error.value = e.response?.data?.message ?? "L'analyse ATS n'est pas disponible pour le moment.";
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="border rounded-lg p-3 space-y-2">
        <h3 class="text-sm font-semibold text-gray-800">Analyse ATS</h3>
        <textarea
            v-model="jobOffer"
            rows="4"
            placeholder="Colle ici le texte de l'offre d'emploi à comparer..."
            class="w-full text-xs rounded border-gray-300"
        ></textarea>
        <button
            @click="analyze"
            :disabled="isLoading"
            class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded font-medium disabled:opacity-50"
        >
            {{ isLoading ? 'Analyse en cours…' : 'Analyser la compatibilité' }}
        </button>

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
        <div v-if="result" class="text-xs text-gray-700 whitespace-pre-wrap bg-gray-50 rounded p-2 max-h-64 overflow-y-auto">
            {{ result }}
        </div>
    </div>
</template>
