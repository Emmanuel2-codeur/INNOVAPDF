<script setup lang="ts">
import type { DocumentSchema } from '@/types/document';

defineProps<{
    doc: DocumentSchema;
}>();
</script>

<template>
    <div class="h-full w-full bg-white flex min-h-[297mm]" :style="{ fontFamily: doc.style.fontFamily }">
        <!-- Sidebar Gauche Colorée -->
        <div class="w-1/3 p-6 text-white space-y-6" :style="{ backgroundColor: doc.style.primaryColor }">
            <div class="text-center space-y-2">
                <h1 class="text-xl font-bold uppercase tracking-wide">
                    {{ doc.profile?.fullName || 'Nom Prénom' }}
                </h1>
                <p class="text-xs font-medium opacity-90">
                    {{ doc.profile?.title || 'Titre du poste' }}
                </p>
            </div>

            <!-- Coordonnées -->
            <div class="text-xs space-y-3 border-t border-white/20 pt-4">
                <h3 class="font-bold uppercase tracking-wider text-[10px]">Contact</h3>
                <p v-if="doc.profile?.email" class="break-all">📧 {{ doc.profile.email }}</p>
                <p v-if="doc.profile?.phone">📞 {{ doc.profile.phone }}</p>
                <p v-if="doc.profile?.location">📍 {{ doc.profile.location }}</p>
            </div>

            <!-- Résumé / Profil -->
            <div v-if="doc.profile?.summary" class="text-xs space-y-2 border-t border-white/20 pt-4">
                <h3 class="font-bold uppercase tracking-wider text-[10px]">À Propos</h3>
                <p class="opacity-90 leading-relaxed whitespace-pre-line">{{ doc.profile.summary }}</p>
            </div>
        </div>

        <!-- Colonne Principale Droite -->
        <div class="w-2/3 p-6 space-y-6">
            <section v-if="doc.experiences && doc.experiences.length">
                <h2 
                    class="text-sm font-bold uppercase tracking-wider border-b-2 pb-1 mb-4" 
                    :style="{ borderColor: doc.style.primaryColor, color: doc.style.primaryColor }"
                >
                    Expériences Professionnelles
                </h2>
                
                <div class="space-y-4">
                    <div v-for="exp in doc.experiences" :key="exp.id">
                        <div class="flex justify-between items-baseline">
                            <h3 class="font-semibold text-gray-900 text-sm">{{ exp.position }}</h3>
                            <span class="text-xs text-gray-500 font-medium">
                                {{ exp.startDate }} - {{ exp.endDate || 'Présent' }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-600">{{ exp.company }}</p>
                        <p class="text-xs text-gray-600 mt-1 whitespace-pre-line leading-relaxed">
                            {{ exp.description }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>