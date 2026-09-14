<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MonochromeCV from '@/components/Templates/CV/Monochrome.vue';
import BannerPhotoCV from '@/components/Templates/CV/BannerPhoto.vue';
import DarkSidebarCV from '@/components/Templates/CV/DarkSidebar.vue';
import CircularPhotoCV from '@/components/Templates/CV/CircularPhoto.vue';
import InvoicePurple from '@/components/Templates/Invoice/Purple.vue';
import InvoiceOrangeBlack from '@/components/Templates/Invoice/OrangeBlack.vue';
import InvoiceRedWave from '@/components/Templates/Invoice/RedWave.vue';
import InvoiceBlueClean from '@/components/Templates/Invoice/BlueClean.vue';
import CoverLetterRoundedGreen from '@/components/Templates/CoverLetter/RoundedGreen.vue';
import CoverLetterOrganicOrange from '@/components/Templates/CoverLetter/OrganicOrange.vue';
import CertificateGoldBlack from '@/components/Templates/Certificate/GoldBlack.vue';
import CertificateBlueGold from '@/components/Templates/Certificate/BlueGold.vue';
import type { DocumentSchema } from '@/types/document';

const props = defineProps<{
    document: DocumentSchema & { content: Record<string, unknown> };
    token: string;
}>();

const doc = {
    ...props.document,
    profile: props.document.content?.profile,
    experiences: props.document.content?.experiences,
    education: props.document.content?.education,
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

        <div :class="['w-full bg-white shadow-lg overflow-hidden', ['attestation', 'certificate'].includes(doc.type) ? 'max-w-4xl aspect-[297/210]' : 'max-w-3xl aspect-[210/297]']">
            <MonochromeCV v-if="doc.type === 'cv' && doc.template === 'monochrome'" :doc="doc" />
            <BannerPhotoCV v-if="doc.type === 'cv' && doc.template === 'photo-banner'" :doc="doc" />
            <DarkSidebarCV v-if="doc.type === 'cv' && doc.template === 'dark-sidebar'" :doc="doc" />
            <CircularPhotoCV v-if="doc.type === 'cv' && doc.template === 'circular-photo'" :doc="doc" />
            <CoverLetterRoundedGreen v-if="doc.type === 'cover_letter' && doc.template === 'rounded-green'" :doc="doc" />
            <CoverLetterOrganicOrange v-if="doc.type === 'cover_letter' && doc.template === 'organic-orange'" :doc="doc" />
            <InvoicePurple v-if="['invoice','quote'].includes(doc.type) && doc.template === 'purple'" :doc="doc" />
            <InvoiceOrangeBlack v-if="['invoice','quote'].includes(doc.type) && doc.template === 'orange-black'" :doc="doc" />
            <InvoiceRedWave v-if="['invoice','quote'].includes(doc.type) && doc.template === 'red-wave'" :doc="doc" />
            <InvoiceBlueClean v-if="['invoice','quote'].includes(doc.type) && doc.template === 'blue-clean'" :doc="doc" />
            <CertificateGoldBlack v-if="['attestation','certificate'].includes(doc.type) && doc.template === 'gold-black'" :doc="doc" />
            <CertificateBlueGold v-if="['attestation','certificate'].includes(doc.type) && doc.template === 'blue-gold'" :doc="doc" />
        </div>
    </div>
</template>
