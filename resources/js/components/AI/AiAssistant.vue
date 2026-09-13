<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    modelValue: string | undefined;
}>();

const emit = defineEmits<{ 'update:modelValue': [string] }>();

const isOpen = ref(false);
const isLoading = ref(false);
const error = ref<string | null>(null);
const suggestion = ref<string | null>(null);

const actions: { key: string; label: string }[] = [
    { key: 'correct', label: 'Corriger' },
    { key: 'reformulate', label: 'Reformuler' },
    { key: 'shorten', label: 'Raccourcir' },
    { key: 'expand', label: 'Développer' },
];

const runAction = async (action: string) => {
    if (!props.modelValue?.trim()) {
        error.value = 'Rien à traiter : le champ est vide.';
        return;
    }

    isLoading.value = true;
    error.value = null;
    suggestion.value = null;

    try {
        const { data } = await axios.post('/ai/transform', { text: props.modelValue, action });
        suggestion.value = data.result;
    } catch (e: any) {
        error.value = e.response?.data?.message ?? "L'assistant IA n'est pas disponible pour le moment.";
    } finally {
        isLoading.value = false;
    }
};

// Insertion directe du résultat seulement après validation explicite (cahier des charges §6).
const apply = () => {
    if (suggestion.value === null) return;
    emit('update:modelValue', suggestion.value);
    suggestion.value = null;
    isOpen.value = false;
};

const reject = () => {
    suggestion.value = null;
};
</script>

<template>
    <div class="relative inline-block">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="text-xs text-indigo-600 hover:underline font-medium inline-flex items-center gap-1"
        >
            ✨ Assistant IA
        </button>

        <div
            v-if="isOpen"
            class="absolute z-20 mt-1 w-72 bg-white border rounded-lg shadow-lg p-3 text-left"
        >
            <div class="flex flex-wrap gap-1 mb-2">
                <button
                    v-for="a in actions"
                    :key="a.key"
                    @click="runAction(a.key)"
                    :disabled="isLoading"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded disabled:opacity-50"
                >
                    {{ a.label }}
                </button>
            </div>

            <p v-if="isLoading" class="text-xs text-gray-400">Génération en cours…</p>
            <p v-if="error" class="text-xs text-red-600">{{ error }}</p>

            <div v-if="suggestion" class="mt-2 border-t pt-2">
                <p class="text-xs text-gray-700 whitespace-pre-wrap max-h-40 overflow-y-auto">{{ suggestion }}</p>
                <div class="flex gap-2 mt-2">
                    <button @click="apply" class="text-xs bg-green-600 text-white px-3 py-1 rounded font-medium">
                        Appliquer
                    </button>
                    <button @click="reject" class="text-xs bg-gray-200 px-3 py-1 rounded">
                        Rejeter
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
