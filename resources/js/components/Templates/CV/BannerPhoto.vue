<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';
import { Facebook, Mail, Phone } from 'lucide-vue-next';

defineProps<{ doc: DocumentSchema }>();
</script>

<template>
    <div class="h-full w-full bg-white text-gray-800 flex flex-col" :style="{ fontFamily: doc.style.fontFamily }">
        <div class="p-8 text-white flex items-center justify-between" :style="{ backgroundColor: doc.style.primaryColor }">
            <div>
                <h1 class="text-2xl font-extrabold uppercase leading-tight">{{ doc.profile?.fullName || 'PRÉNOM NOM' }}</h1>
                <p class="text-sm opacity-90 mt-1">{{ doc.profile?.title || 'Titre du poste' }}</p>
            </div>
            <img
                v-if="doc.profile?.photoUrl"
                :src="doc.profile.photoUrl"
                class="w-20 h-20 rounded-full object-cover border-2 border-white/40 shrink-0"
                alt="Photo"
            />
        </div>

        <div class="p-8 flex-1 space-y-6 text-xs">
            <div v-if="doc.profile?.summary">
                <h2 class="font-bold uppercase tracking-wide mb-1.5 border-l-4 pl-2" :style="{ borderColor: doc.style.primaryColor }">Profil</h2>
                <p class="text-gray-600 leading-relaxed">{{ doc.profile.summary }}</p>
            </div>

            <div v-if="doc.education?.length">
                <h2 class="font-bold uppercase tracking-wide mb-2 border-l-4 pl-2" :style="{ borderColor: doc.style.primaryColor }">Éducation</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div v-for="edu in doc.education" :key="edu.id">
                        <p class="font-semibold">{{ edu.startDate }} - {{ edu.endDate }} : {{ edu.degree }}</p>
                        <p class="text-gray-500">{{ edu.school }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div v-if="doc.experiences?.length">
                    <h2 class="font-bold uppercase tracking-wide mb-2 border-l-4 pl-2" :style="{ borderColor: doc.style.primaryColor }">Expériences</h2>
                    <div v-for="exp in doc.experiences" :key="exp.id" class="mb-2">
                        <p class="font-semibold">{{ exp.startDate }} - {{ exp.endDate || 'Présent' }} : {{ exp.position }}</p>
                        <p class="text-gray-500">{{ exp.company }}</p>
                    </div>
                </div>
                <div v-if="doc.profile?.skills?.length">
                    <h2 class="font-bold uppercase tracking-wide mb-2 border-l-4 pl-2" :style="{ borderColor: doc.style.primaryColor }">Compétences</h2>
                    <ul class="list-disc list-inside text-gray-600 space-y-0.5">
                        <li v-for="(skill, i) in doc.profile.skills" :key="i">{{ skill }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="p-6 text-white text-[10px] flex items-center justify-center gap-6" :style="{ backgroundColor: doc.style.primaryColor }">
            <span v-if="doc.profile?.email" class="flex items-center gap-1"><Mail class="w-3 h-3" />{{ doc.profile.email }}</span>
            <span v-if="doc.profile?.phone" class="flex items-center gap-1"><Phone class="w-3 h-3" />{{ doc.profile.phone }}</span>
        </div>
    </div>
</template>
