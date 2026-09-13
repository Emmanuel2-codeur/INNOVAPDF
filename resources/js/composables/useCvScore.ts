import { computed, type Ref } from 'vue';
import type { DocumentSchema } from '@/types/document';

interface ScoreIssue {
    label: string;
    points: number;
}

/**
 * Score de qualité sur 100 + liste des points à améliorer.
 * Heuristique simple, calculée côté client (pas d'appel IA nécessaire ici) :
 * chaque critère manquant ou faible retire des points, cumulés au départ de 100.
 */
export function useCvScore(doc: Ref<DocumentSchema>) {
    const result = computed(() => {
        const issues: ScoreIssue[] = [];
        const profile = doc.value.profile;
        const experiences = doc.value.experiences ?? [];

        if (!profile?.photoUrl) issues.push({ label: 'Aucune photo de profil', points: 5 });
        if (!profile?.title) issues.push({ label: 'Titre de poste manquant', points: 10 });
        if (!profile?.summary || profile.summary.trim().length < 40) {
            issues.push({ label: 'Résumé professionnel absent ou trop court', points: 15 });
        }
        if (!profile?.phone) issues.push({ label: 'Numéro de téléphone manquant', points: 5 });
        if (!profile?.location) issues.push({ label: 'Localisation manquante', points: 5 });

        if (experiences.length === 0) {
            issues.push({ label: "Aucune expérience renseignée", points: 30 });
        } else {
            const weak = experiences.filter(e => !e.description || e.description.trim().length < 40);
            if (weak.length > 0) {
                issues.push({ label: `${weak.length} expérience(s) avec une description trop courte`, points: Math.min(20, weak.length * 8) });
            }
            const noDates = experiences.filter(e => !e.startDate);
            if (noDates.length > 0) {
                issues.push({ label: `${noDates.length} expérience(s) sans date de début`, points: 5 });
            }
        }

        const totalDeduction = issues.reduce((sum, i) => sum + i.points, 0);
        const score = Math.max(0, 100 - totalDeduction);

        return { score, issues };
    });

    return { score: computed(() => result.value.score), issues: computed(() => result.value.issues) };
}
