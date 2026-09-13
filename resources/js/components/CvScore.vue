<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    score: number;
    issues: { label: string; points: number }[];
}>();

const colorClass = computed(() => {
    if (props.score >= 80) return 'text-green-600 border-green-200 bg-green-50';
    if (props.score >= 50) return 'text-amber-600 border-amber-200 bg-amber-50';
    return 'text-red-600 border-red-200 bg-red-50';
});
</script>

<template>
    <div :class="['border rounded-lg p-3 text-sm', colorClass]">
        <div class="flex items-center justify-between">
            <span class="font-semibold">Score de qualité</span>
            <span class="text-lg font-bold">{{ score }}/100</span>
        </div>
        <ul v-if="issues.length > 0" class="mt-2 space-y-1 text-xs opacity-90">
            <li v-for="(issue, i) in issues" :key="i">• {{ issue.label }} (-{{ issue.points }})</li>
        </ul>
        <p v-else class="mt-2 text-xs">Excellent ! Aucun point d'amélioration détecté.</p>
    </div>
</template>
