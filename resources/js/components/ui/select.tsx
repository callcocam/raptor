import * as React from "react";
import { cn } from "../../lib/utils";

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

export { Select, DropdownFilter }; 