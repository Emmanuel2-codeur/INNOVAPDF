import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { DocumentSchema, ExperienceItem, StyleConfig } from '@/types/document';

export const useDocumentStore = defineStore('document', () => {
    // État réactif principal
    const currentDocument = ref<DocumentSchema>({
        type: 'cv',
        title: 'Mon Document',
        template: 'minimal',
        profile: {
            fullName: '',
            title: '',
            email: '',
            phone: '',
            location: '',
            summary: '',
        },
        experiences: [],
        education: [],
        invoiceItems: [],
        invoiceMeta: {
            taxRate: 0.18,
            currency: 'FCFA',
        },
        coverLetter: {
            recipientName: '',
            recipientCompany: '',
            subject: '',
            body: '',
        },
        certificate: {
            recipientName: '',
            purpose: '',
            body: '',
            issueDate: '',
            issuerName: '',
            issuerTitle: '',
        },
        style: {
            primaryColor: '#4f46e5',
            secondaryColor: '#64748b',
            fontFamily: 'Inter',
            fontSize: 'md',
            margins: 20,
        },
    });

    // Actions pour modifier le document
    function setDocument(doc: DocumentSchema) {
        currentDocument.value = doc;
    }

    function updateProfile(field: keyof Required<DocumentSchema>['profile'], value: string) {
        if (currentDocument.value.profile) {
            currentDocument.value.profile[field] = value;
        }
    }

    function updateStyle(newStyle: Partial<StyleConfig>) {
        currentDocument.value.style = { ...currentDocument.value.style, ...newStyle };
    }

    // Gestion des expériences (CV)
    function addExperience(exp: ExperienceItem) {
        if (!currentDocument.value.experiences) {
            currentDocument.value.experiences = [];
        }
        currentDocument.value.experiences.push(exp);
    }

    function removeExperience(id: string) {
        if (currentDocument.value.experiences) {
            currentDocument.value.experiences = currentDocument.value.experiences.filter(exp => exp.id !== id);
        }
    }

    function addEmptyExperience() {
        if (!currentDocument.value.experiences) {
            currentDocument.value.experiences = [];
        }
        currentDocument.value.experiences.push({
            id: crypto.randomUUID(),
            company: '',
            position: '',
            startDate: '',
            endDate: '',
            description: '',
        });
    }

    // Gestion de l'éducation (CV)
    function addEmptyEducation() {
        if (!currentDocument.value.education) {
            currentDocument.value.education = [];
        }
        currentDocument.value.education.push({
            id: crypto.randomUUID(),
            degree: '',
            school: '',
            startDate: '',
            endDate: '',
        });
    }

    function removeEducation(id: string) {
        if (currentDocument.value.education) {
            currentDocument.value.education = currentDocument.value.education.filter(edu => edu.id !== id);
        }
    }

    // Gestion des lignes de facturation (Facture)
    function addEmptyInvoiceItem() {
        if (!currentDocument.value.invoiceItems) {
            currentDocument.value.invoiceItems = [];
        }
        currentDocument.value.invoiceItems.push({
            id: crypto.randomUUID(),
            description: '',
            quantity: 1,
            unitPrice: 0,
            total: 0,
        });
    }

    function removeInvoiceItem(id: string) {
        if (currentDocument.value.invoiceItems) {
            currentDocument.value.invoiceItems = currentDocument.value.invoiceItems.filter(item => item.id !== id);
        }
    }

    return {
        currentDocument,
        setDocument,
        updateProfile,
        updateStyle,
        addExperience,
        removeExperience,
        addEmptyExperience,
        addEmptyEducation,
        removeEducation,
        addEmptyInvoiceItem,
        removeInvoiceItem,
    };
});