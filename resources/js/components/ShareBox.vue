<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import QRCode from 'qrcode';

const props = defineProps<{
    documentId: number | null;
    shareUrl: string | null;
}>();

const qrDataUrl = ref<string | null>(null);
const copied = ref(false);

const renderQr = async (url: string | null) => {
    qrDataUrl.value = url ? await QRCode.toDataURL(url, { width: 160, margin: 1 }) : null;
};

watch(() => props.shareUrl, renderQr, { immediate: true });

const enableSharing = () => {
    if (!props.documentId) return;
    router.post(`/documents/${props.documentId}/share`, {}, { preserveScroll: true });
};

const disableSharing = () => {
    if (!props.documentId) return;
    if (!confirm("Désactiver le partage ? L'ancien lien ne fonctionnera plus.")) return;
    router.delete(`/documents/${props.documentId}/share`, { preserveScroll: true });
};

const copyLink = async () => {
    if (!props.shareUrl) return;
    await navigator.clipboard.writeText(props.shareUrl);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};
</script>

<template>
    <div class="border rounded-lg p-3 space-y-2">
        <h3 class="text-sm font-semibold text-gray-800">Partage</h3>

        <div v-if="!shareUrl">
            <p class="text-xs text-gray-500 mb-2">
                Génère un lien privé : seules les personnes qui le possèdent peuvent voir ce document, sans créer de compte.
            </p>
            <button @click="enableSharing" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded font-medium">
                Activer le partage
            </button>
        </div>

        <div v-else class="space-y-2">
            <div class="flex items-center gap-2">
                <input :value="shareUrl" readonly class="flex-1 text-xs rounded border-gray-300 bg-gray-50" />
                <button @click="copyLink" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1.5 rounded shrink-0">
                    {{ copied ? 'Copié !' : 'Copier' }}
                </button>
            </div>
            <img v-if="qrDataUrl" :src="qrDataUrl" alt="QR code de partage" class="w-24 h-24" />
            <button @click="disableSharing" class="text-xs text-red-600 hover:underline">
                Désactiver le partage
            </button>
        </div>
    </div>
</template>
