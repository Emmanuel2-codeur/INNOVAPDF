export type DocumentType = 'cv' | 'cover_letter' | 'invoice' | 'quote' | 'certificate' | 'attestation';

export interface ProfileData {
    fullName: string;
    title: string;
    email: string;
    phone: string;
    location: string;
    photoUrl?: string;
    summary?: string;
    skills?: string[];
}

export interface EducationItem {
    id: string;
    degree: string;
    school: string;
    startDate: string;
    endDate?: string;
}

export interface ExperienceItem {
    id: string;
    company: string;
    position: string;
    startDate: string;
    endDate?: string;
    description: string;
}

export interface InvoiceItem {
    id: string;
    description: string;
    quantity: number;
    unitPrice: number;
    total: number;
}

export interface CertificateData {
    recipientName: string;
    purpose: string;
    body: string;
    issueDate: string;
    issuerName: string;
    issuerTitle: string;
}

export interface CoverLetterData {
    recipientName: string;
    recipientCompany: string;
    subject: string;
    body: string;
}

export interface InvoiceMeta {
    taxRate: number; // ex: 0.18 pour 18%
    currency: string; // ex: 'FCFA', 'EUR', 'USD'
}

export interface StyleConfig {
    primaryColor: string;
    secondaryColor?: string;
    fontFamily: string;
    fontSize: 'sm' | 'md' | 'lg';
    margins: number;
}

export interface DocumentSchema {
    id?: number;
    type: DocumentType;
    title: string;
    template: string;
    profile?: ProfileData;
    experiences?: ExperienceItem[];
    education?: EducationItem[];
    invoiceItems?: InvoiceItem[];
    invoiceMeta?: InvoiceMeta;
    coverLetter?: CoverLetterData;
    certificate?: CertificateData;
    style: StyleConfig;
}