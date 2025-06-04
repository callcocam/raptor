import * as React from "react";
import { cn } from "../../lib/utils";
import { Check, ChevronDown, X } from "lucide-react";

export interface SelectProps
  extends React.SelectHTMLAttributes<HTMLSelectElement> {}

const Select = React.forwardRef<HTMLSelectElement, SelectProps>(
  ({ className, children, ...props }, ref) => {
    return (
      <select
        className={cn(
          "flex h-9 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50",
          className
        )}
        ref={ref}
        {...props}
      >
        {children}
      </select>
    );
  }
);
Select.displayName = "Select";

// Componente Dropdown customizado para filtros
interface DropdownFilterProps {
  label: string;
  value: string;
  options: Array<{
    label: string;
    value: string;
    count?: number;
  }>;
  onChange: (value: string) => void;
  showCounts?: boolean;
}

const DropdownFilter = ({
  label,
  value,
  options,
  onChange,
  showCounts = true
}: DropdownFilterProps) => {
  const [isOpen, setIsOpen] = React.useState(false);
  const [selectedLabel, setSelectedLabel] = React.useState('');

  React.useEffect(() => {
    const selected = options.find(option => option.value === value);
    setSelectedLabel(selected ? selected.label : 'Todos');
  }, [value, options]);

  return (
    <div className="relative">
      <button
        type="button"
        className="flex h-9 w-full min-w-[120px] items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        onClick={() => setIsOpen(!isOpen)}
      >
        <div className="flex items-center gap-2">
          <span className="text-muted-foreground">{label}:</span>
          <span>{selectedLabel}</span>
        </div>
        <span className="text-xs text-muted-foreground">▼</span>
      </button>

      {isOpen && (
        <div className="absolute top-full left-0 z-50 w-full min-w-[200px] mt-1 rounded-md border bg-popover text-popover-foreground shadow-md">
          <div className="p-1">
            {/* Opção "Todos" */}
            <button
              type="button"
              className={cn(
                "relative flex w-full items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground",
                !value && "bg-accent text-accent-foreground"
              )}
              onClick={() => {
                onChange('');
                setIsOpen(false);
              }}
            >
              <span>Todos</span>
            </button>

            {/* Opções com contadores */}
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                className={cn(
                  "relative flex w-full items-center justify-between rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground",
                  value === option.value && "bg-accent text-accent-foreground"
                )}
                onClick={() => {
                  onChange(option.value);
                  setIsOpen(false);
                }}
              >
                <span>{option.label}</span>
                {showCounts && option.count !== undefined && (
                  <span className="ml-2 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                    {option.count}
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

// 🔥 NOVO: Componente MultiSelectFilter Avançado como na imagem
interface MultiSelectFilterProps {
  label: string;
  values: string[];
  options: Array<{
    label: string;
    value: string;
    count?: number;
  }>;
  onChange: (values: string[]) => void;
  showCounts?: boolean;
  placeholder?: string;
}

const MultiSelectFilter = ({
  label,
  values = [],
  options,
  onChange,
  showCounts = true,
  placeholder = "Select options..."
}: MultiSelectFilterProps) => {
  const [isOpen, setIsOpen] = React.useState(false);
  const dropdownRef = React.useRef<HTMLDivElement>(null);

  // Fechar dropdown quando clicar fora
  React.useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Função para toggle de uma opção
  const toggleOption = (value: string) => {
    const newValues = values.includes(value)
      ? values.filter(v => v !== value)
      : [...values, value];
    onChange(newValues);
  };

  // Função para limpar filtros
  const clearFilters = () => {
    onChange([]);
  };

  // Texto do trigger
  const getTriggerText = () => {
    if (values.length === 0) {
      return placeholder;
    }
    if (values.length === 1) {
      const selected = options.find(opt => opt.value === values[0]);
      return selected?.label || values[0];
    }
    return `${values.length} selected`;
  };

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        type="button"
        className="flex h-9 w-full min-w-[140px] items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        onClick={() => setIsOpen(!isOpen)}
      >
        <div className="flex items-center gap-2">
          <span className="text-muted-foreground font-medium">{label}</span>
          {values.length > 0 && (
            <span className="text-xs bg-primary text-primary-foreground px-2 py-0.5 rounded-full">
              {values.length}
            </span>
          )}
        </div>
        <ChevronDown className={cn("h-4 w-4 transition-transform", isOpen && "rotate-180")} />
      </button>

      {isOpen && (
        <div className="absolute top-full left-0 z-50 w-full min-w-[220px] mt-1 rounded-md border bg-popover text-popover-foreground shadow-lg">
          {/* Header do dropdown */}
          <div className="p-3 border-b">
            <div className="flex items-center justify-between">
              <span className="font-medium text-sm">{label}</span>
              {values.length > 0 && (
                <button
                  type="button"
                  onClick={clearFilters}
                  className="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1"
                >
                  <X className="h-3 w-3" />
                  Clear filters
                </button>
              )}
            </div>
          </div>

          {/* Lista de opções com checkboxes */}
          <div className="p-1 max-h-64 overflow-y-auto">
            {options.map((option) => {
              const isSelected = values.includes(option.value);
              
              return (
                <button
                  key={option.value}
                  type="button"
                  className="relative flex w-full items-center gap-3 rounded-sm px-3 py-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground"
                  onClick={() => toggleOption(option.value)}
                >
                  {/* Checkbox customizado */}
                  <div className={cn(
                    "flex h-4 w-4 items-center justify-center rounded border border-input",
                    isSelected && "bg-primary border-primary text-primary-foreground"
                  )}>
                    {isSelected && <Check className="h-3 w-3" />}
                  </div>

                  {/* Label e contador */}
                  <div className="flex flex-1 items-center justify-between">
                    <span>{option.label}</span>
                    {showCounts && option.count !== undefined && (
                      <span className="ml-2 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                        {option.count}
                      </span>
                    )}
                  </div>
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

export { Select, DropdownFilter, MultiSelectFilter }; 