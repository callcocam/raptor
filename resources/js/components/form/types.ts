interface HeaderAction {
  label: string;
  icon: string;
  route: string;
  color?: string;
  class?: string;
  target?: string;
  component?: string;
  iconSize?: number;
  method?: 'get' | 'post' | 'put' | 'delete' | 'patch';
}


interface FilterOption {
  label: string
  value: string
  icon?: any
}

interface Confirmation {
  title: string
  description?: string
  confirmText?: string
  cancelText?: string
  variant?: 'default' | 'destructive'
}

interface Action {
  label: string
  action: string
  icon?: string
  variant?: 'link' | 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost'
  shortcut?: string
  route?: string
  routeParams?: Record<string, any>
  href?: string
  confirmation?: Confirmation,
  method?: 'post' | 'delete' | 'put' | 'patch' | 'get'
}

export interface FormField {
  id?: string
  name: string
  type: string
  description?: string
  component: string
  label?: string
  props?: Record<string, any>
  items?: Array<Record<string, any>>
  options?: FilterOption[]
  fields?: FormField[]
  grid?: number | Array<Record<string, number>>
  mask?: string
  maskOptions?: Record<string, any>
  unmaskedValue?: boolean
  value: any
  rules?: string[]
  returnMasked: boolean
  min?: number
  max?: number
  minLength?: number
  maxLength?: number
  minValue?: number
  maxValue?: number

}

export interface FormSection {
  name: string
  description?: string
  label?: string
  fields: FormField[]
  layout?: number | Array<Record<string, number>>
  grid?: number | Array<Record<string, number>>
}

export interface FormRepeater {
  name: string
  label?: string
  description?: string
  fields: FormField[]
  layout?: number | Array<Record<string, number>>
  grid?: number | Array<Record<string, number>>
  min?: number
  max?: number
  addText?: string
  removeText?: string
  addIcon?: string
  removeIcon?: string
  addButtonVariant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost'
  removeButtonVariant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost'
  sortable?: boolean // New property to control reordering functionality
}

export interface FormConfig {
  layout?: number | Array<Record<string, number>>
  sections: FormSection[]
  submitText?: string
  cancelText?: string
  cancelRoute?: string
  cancelRouteParams?: Record<string, any>
  submitRoute?: string
  submitRouteParams?: Record<string, any>
  submitMethod?: 'post' | 'put' | 'patch'
  submitConfirmation?: Confirmation
  modelLabel?: string
  modelPluralLabel?: string
  modelDescription?: string
  routeName?: string
  fullWidth?: boolean
}

export type { FilterOption, Action, Confirmation, HeaderAction, }
