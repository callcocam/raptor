import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    modelLabel?: string;
    navigationIcon?: string;
    modelPluralName?: string;
    navigationGroup?: string;
    navigationGroupIcon?: string;
    items?: NavItem[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Tenant {
    id: number;
    name: string;
    slug: string;
    domain: string;
    preefix?: string;
    logo?: string;
    database?: string;
    status: string;
    description?: string;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Column {
  field: string
  label: string
  sortable?: boolean
  // Add other column properties as needed
}

export interface BulkActionPayload {
  action: string
  model: string
  items?: any[]
  filters?: Record<string, any>
}

export type ButtonVariant = 'default' | 'link' | 'secondary' | 'destructive' | 'outline' | 'ghost'

export interface Action {
  label: string
  action: string
  method: string
  variant?: ButtonVariant
  // Add other action properties as needed
}
