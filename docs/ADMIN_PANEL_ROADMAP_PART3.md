# 🗺️ Roadmap Part 3 - Composants, Tests & Documentation

> **Continuation de** : ADMIN_PANEL_ROADMAP_PART2.md
> **Design System** : Monologue (dark-themed, minimalist)
> **Finalisation** : Composants restants, tests, documentation, seeders

---

## 🧩 I. COMPOSANTS RESTANTS (Design Monologue)

### 1.1 Composant : RoleSelector

**Fichier** : `resources/js/components/admin/RoleSelector.tsx`

```typescript
import { Fragment } from 'react';
import { Listbox, Transition } from '@headlessui/react';
import { ChevronDown, Check, Shield } from 'lucide-react';
import type { UserRole } from '@/types';

interface RoleSelectorProps {
  value: UserRole;
  onChange: (role: UserRole) => void;
  roles: Array<{
    value: string;
    label: string;
    description: string;
  }>;
  error?: string;
  disabled?: boolean;
}

export function RoleSelector({
  value,
  onChange,
  roles,
  error,
  disabled = false,
}: RoleSelectorProps) {
  const selectedRole = roles.find((r) => r.value === value);

  return (
    <div>
      <label className="mb-2 block font-mono text-xs text-foreground">
        User Role
      </label>

      <Listbox value={value} onChange={onChange} disabled={disabled}>
        <div className="relative">
          <Listbox.Button
            className={`
              relative w-full rounded-sm border px-3 py-2.5 text-left
              font-mono text-sm transition-colors
              ${
                error
                  ? 'border-red-500 bg-background text-foreground'
                  : 'border-border bg-background text-foreground hover:border-brand-primary'
              }
              ${disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'}
              focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-0
            `}
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <Shield className="h-4 w-4 text-brand-primary" />
                <span>{selectedRole?.label || 'Select a role'}</span>
              </div>
              <ChevronDown className="h-4 w-4 text-foreground-muted" />
            </div>
          </Listbox.Button>

          <Transition
            as={Fragment}
            leave="transition ease-in duration-100"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <Listbox.Options
              className="
                absolute z-10 mt-1 max-h-60 w-full overflow-auto
                rounded-sm border border-border bg-background-elevated
                py-1 shadow-lg focus:outline-none
              "
            >
              {roles.map((role) => (
                <Listbox.Option
                  key={role.value}
                  value={role.value}
                  className={({ active }) =>
                    `
                    relative cursor-pointer select-none px-3 py-2.5
                    transition-colors
                    ${
                      active
                        ? 'bg-background-muted text-foreground'
                        : 'text-foreground-secondary'
                    }
                  `
                  }
                >
                  {({ selected, active }) => (
                    <div className="flex items-start justify-between gap-3">
                      <div className="flex-1">
                        <div className="flex items-center gap-2">
                          <span
                            className={`
                              font-mono text-sm
                              ${selected ? 'text-brand-primary' : ''}
                            `}
                          >
                            {role.label}
                          </span>
                          {selected && (
                            <Check className="h-4 w-4 text-brand-primary" />
                          )}
                        </div>
                        <p className="mt-1 font-mono text-xs text-foreground-muted">
                          {role.description}
                        </p>
                      </div>
                    </div>
                  )}
                </Listbox.Option>
              ))}
            </Listbox.Options>
          </Transition>
        </div>
      </Listbox>

      {error && (
        <p className="mt-1 font-mono text-xs text-red-400">{error}</p>
      )}

      {selectedRole && !error && (
        <p className="mt-1 font-mono text-xs text-foreground-muted">
          {selectedRole.description}
        </p>
      )}
    </div>
  );
}
```

---

### 1.2 Composant : PermissionManager

**Fichier** : `resources/js/components/admin/PermissionManager.tsx`

```typescript
import { useState, useEffect } from 'react';
import { Badge } from '@/components/ui/Badge';
import { ChevronDown, ChevronRight, Shield, Lock, Unlock } from 'lucide-react';
import type { UserRole } from '@/types';

interface PermissionManagerProps {
  role: UserRole;
  permissions: string[];
  onChange: (permissions: string[]) => void;
}

interface PermissionCategory {
  name: string;
  permissions: Array<{
    value: string;
    label: string;
    description: string;
  }>;
}

const PERMISSION_CATEGORIES: PermissionCategory[] = [
  {
    name: 'Users',
    permissions: [
      { value: 'users.view', label: 'View Users', description: 'View user list and details' },
      { value: 'users.create', label: 'Create Users', description: 'Create new users' },
      { value: 'users.edit', label: 'Edit Users', description: 'Modify user information' },
      { value: 'users.delete', label: 'Delete Users', description: 'Delete users from system' },
      { value: 'users.manage_roles', label: 'Manage Roles', description: 'Change user roles and permissions' },
    ],
  },
  {
    name: 'MCP Servers',
    permissions: [
      { value: 'mcp_servers.view', label: 'View Servers', description: 'View MCP server configurations' },
      { value: 'mcp_servers.create', label: 'Create Servers', description: 'Add new MCP servers' },
      { value: 'mcp_servers.edit', label: 'Edit Servers', description: 'Modify server settings' },
      { value: 'mcp_servers.delete', label: 'Delete Servers', description: 'Remove MCP servers' },
      { value: 'mcp_servers.manage', label: 'Manage Servers', description: 'Full server management access' },
    ],
  },
  {
    name: 'Integrations',
    permissions: [
      { value: 'integrations.view', label: 'View Integrations', description: 'View integration configurations' },
      { value: 'integrations.create', label: 'Create Integrations', description: 'Add new integrations' },
      { value: 'integrations.edit', label: 'Edit Integrations', description: 'Modify integration settings' },
      { value: 'integrations.delete', label: 'Delete Integrations', description: 'Remove integrations' },
      { value: 'integrations.manage_own', label: 'Manage Own', description: 'Manage only own integrations' },
    ],
  },
  {
    name: 'Workflows',
    permissions: [
      { value: 'workflows.view', label: 'View Workflows', description: 'View workflow definitions' },
      { value: 'workflows.create', label: 'Create Workflows', description: 'Create new workflows' },
      { value: 'workflows.edit', label: 'Edit Workflows', description: 'Modify workflows' },
      { value: 'workflows.delete', label: 'Delete Workflows', description: 'Remove workflows' },
      { value: 'workflows.execute', label: 'Execute Workflows', description: 'Run workflows' },
    ],
  },
  {
    name: 'Logs & Settings',
    permissions: [
      { value: 'logs.view', label: 'View Logs', description: 'View system logs' },
      { value: 'logs.export', label: 'Export Logs', description: 'Export log data' },
      { value: 'logs.delete', label: 'Delete Logs', description: 'Delete old logs' },
      { value: 'settings.view', label: 'View Settings', description: 'View system settings' },
      { value: 'settings.edit', label: 'Edit Settings', description: 'Modify system settings' },
    ],
  },
];

export function PermissionManager({
  role,
  permissions,
  onChange,
}: PermissionManagerProps) {
  const [expandedCategories, setExpandedCategories] = useState<string[]>([]);
  const [rolePermissions, setRolePermissions] = useState<string[]>([]);

  useEffect(() => {
    // Get default permissions for the role
    const roleDefaults = getRolePermissions(role);
    setRolePermissions(roleDefaults);
  }, [role]);

  const toggleCategory = (category: string) => {
    setExpandedCategories((prev) =>
      prev.includes(category)
        ? prev.filter((c) => c !== category)
        : [...prev, category]
    );
  };

  const togglePermission = (permission: string) => {
    const newPermissions = permissions.includes(permission)
      ? permissions.filter((p) => p !== permission)
      : [...permissions, permission];

    onChange(newPermissions);
  };

  const isPermissionGranted = (permission: string) => {
    // Check if granted by role or custom permissions
    return rolePermissions.includes(permission) || permissions.includes(permission);
  };

  const isPermissionFromRole = (permission: string) => {
    return rolePermissions.includes(permission);
  };

  return (
    <div>
      <div className="mb-3 flex items-center justify-between">
        <label className="font-mono text-xs text-foreground">
          Permissions
        </label>
        <Badge variant="muted" size="sm">
          {permissions.length} custom
        </Badge>
      </div>

      {/* Role Info */}
      <div className="mb-4 rounded-sm border border-border bg-background p-3">
        <div className="flex items-center gap-2">
          <Shield className="h-4 w-4 text-brand-primary" />
          <span className="font-mono text-xs text-foreground">
            Role: <span className="text-brand-primary">{role}</span>
          </span>
        </div>
        <p className="mt-1 font-mono text-xs text-foreground-muted">
          {rolePermissions.length} permissions granted by role
        </p>
      </div>

      {/* Permission Categories */}
      <div className="space-y-2">
        {PERMISSION_CATEGORIES.map((category) => {
          const isExpanded = expandedCategories.includes(category.name);
          const grantedCount = category.permissions.filter((p) =>
            isPermissionGranted(p.value)
          ).length;

          return (
            <div
              key={category.name}
              className="overflow-hidden rounded-sm border border-border bg-background-secondary"
            >
              {/* Category Header */}
              <button
                onClick={() => toggleCategory(category.name)}
                className="flex w-full items-center justify-between p-3 text-left transition-colors hover:bg-background-elevated"
              >
                <div className="flex items-center gap-2">
                  {isExpanded ? (
                    <ChevronDown className="h-4 w-4 text-foreground-muted" />
                  ) : (
                    <ChevronRight className="h-4 w-4 text-foreground-muted" />
                  )}
                  <span className="font-mono text-sm text-foreground">
                    {category.name}
                  </span>
                </div>
                <Badge variant="muted" size="sm">
                  {grantedCount}/{category.permissions.length}
                </Badge>
              </button>

              {/* Category Permissions */}
              {isExpanded && (
                <div className="border-t border-border bg-background p-3">
                  <div className="space-y-2">
                    {category.permissions.map((permission) => {
                      const isGranted = isPermissionGranted(permission.value);
                      const isFromRole = isPermissionFromRole(permission.value);
                      const isCustom = permissions.includes(permission.value);

                      return (
                        <label
                          key={permission.value}
                          className={`
                            flex cursor-pointer items-start gap-3 rounded-sm
                            p-2 transition-colors
                            ${isGranted ? 'bg-background-elevated' : 'hover:bg-background-elevated'}
                          `}
                        >
                          <input
                            type="checkbox"
                            checked={isCustom}
                            onChange={() => togglePermission(permission.value)}
                            disabled={isFromRole}
                            className="mt-0.5 h-4 w-4 rounded-sm border-border bg-background text-brand-primary focus:ring-brand-primary focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
                          />
                          <div className="flex-1">
                            <div className="flex items-center gap-2">
                              <span
                                className={`
                                  font-mono text-sm
                                  ${isGranted ? 'text-foreground' : 'text-foreground-secondary'}
                                `}
                              >
                                {permission.label}
                              </span>
                              {isFromRole && (
                                <Badge variant="primary" size="sm">
                                  <Lock className="mr-1 h-3 w-3" />
                                  Role
                                </Badge>
                              )}
                              {isCustom && !isFromRole && (
                                <Badge variant="accent" size="sm">
                                  <Unlock className="mr-1 h-3 w-3" />
                                  Custom
                                </Badge>
                              )}
                            </div>
                            <p className="mt-0.5 font-mono text-xs text-foreground-muted">
                              {permission.description}
                            </p>
                          </div>
                        </label>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}

// Helper function
function getRolePermissions(role: UserRole): string[] {
  const rolePermissions: Record<UserRole, string[]> = {
    admin: ['*'], // All permissions
    manager: [
      'mcp_servers.view',
      'mcp_servers.manage',
      'integrations.*',
      'workflows.*',
      'logs.view',
    ],
    user: [
      'mcp_servers.view',
      'integrations.view',
      'integrations.manage_own',
      'workflows.view',
      'workflows.execute',
    ],
    read_only: [
      'mcp_servers.view',
      'integrations.view',
      'workflows.view',
      'logs.view',
    ],
  };

  const permissions = rolePermissions[role] || [];

  // Expand wildcards
  if (permissions.includes('*')) {
    return PERMISSION_CATEGORIES.flatMap((cat) =>
      cat.permissions.map((p) => p.value)
    );
  }

  const expanded: string[] = [];
  permissions.forEach((perm) => {
    if (perm.endsWith('.*')) {
      const category = perm.replace('.*', '');
      const categoryPerms = PERMISSION_CATEGORIES.find(
        (c) => c.name.toLowerCase() === category
      );
      if (categoryPerms) {
        expanded.push(...categoryPerms.permissions.map((p) => p.value));
      }
    } else {
      expanded.push(perm);
    }
  });

  return expanded;
}
```

---

### 1.3 Composant : UserFilters

**Fichier** : `resources/js/components/admin/UserFilters.tsx`

```typescript
import { useState } from 'react';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { Badge } from '@/components/ui/Badge';
import { Search, X, Filter } from 'lucide-react';
import type { UserRole } from '@/types';

interface UserFiltersProps {
  filters: {
    search?: string;
    role?: UserRole;
    is_active?: boolean;
    is_locked?: boolean;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
  };
  roles: Array<{
    value: string;
    label: string;
  }>;
  onChange: (filters: Partial<UserFiltersProps['filters']>) => void;
}

export function UserFilters({ filters, roles, onChange }: UserFiltersProps) {
  const [showAdvanced, setShowAdvanced] = useState(false);

  const activeFilterCount = [
    filters.search,
    filters.role,
    filters.is_active !== undefined,
    filters.is_locked !== undefined,
  ].filter(Boolean).length;

  const clearFilters = () => {
    onChange({
      search: undefined,
      role: undefined,
      is_active: undefined,
      is_locked: undefined,
    });
  };

  return (
    <div className="mb-6 space-y-3">
      {/* Search Bar */}
      <div className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-foreground-muted" />
          <input
            type="text"
            value={filters.search || ''}
            onChange={(e) => onChange({ search: e.target.value })}
            placeholder="Search users by name or email..."
            className="
              w-full rounded-sm border border-border bg-background
              py-2.5 pl-10 pr-3 font-mono text-sm text-foreground
              placeholder:text-foreground-muted
              focus:border-brand-primary focus:outline-none focus:ring-2
              focus:ring-brand-primary focus:ring-offset-0
            "
          />
        </div>

        <Button
          variant={showAdvanced ? 'primary' : 'ghost'}
          size="md"
          onClick={() => setShowAdvanced(!showAdvanced)}
          leftIcon={<Filter className="h-4 w-4" />}
        >
          Filters
          {activeFilterCount > 0 && (
            <Badge variant="accent" size="sm" className="ml-2">
              {activeFilterCount}
            </Badge>
          )}
        </Button>

        {activeFilterCount > 0 && (
          <Button
            variant="ghost"
            size="md"
            onClick={clearFilters}
            leftIcon={<X className="h-4 w-4" />}
          >
            Clear
          </Button>
        )}
      </div>

      {/* Advanced Filters */}
      {showAdvanced && (
        <div className="rounded-md border border-border bg-background-secondary p-4">
          <div className="grid grid-cols-3 gap-4">
            {/* Role Filter */}
            <div>
              <label className="mb-2 block font-mono text-xs text-foreground">
                Role
              </label>
              <select
                value={filters.role || ''}
                onChange={(e) =>
                  onChange({ role: e.target.value as UserRole | undefined })
                }
                className="
                  w-full rounded-sm border border-border bg-background
                  px-3 py-2 font-mono text-sm text-foreground
                  focus:border-brand-primary focus:outline-none
                  focus:ring-2 focus:ring-brand-primary focus:ring-offset-0
                "
              >
                <option value="">All Roles</option>
                {roles.map((role) => (
                  <option key={role.value} value={role.value}>
                    {role.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Status Filter */}
            <div>
              <label className="mb-2 block font-mono text-xs text-foreground">
                Account Status
              </label>
              <select
                value={
                  filters.is_active === undefined
                    ? ''
                    : filters.is_active
                    ? 'active'
                    : 'inactive'
                }
                onChange={(e) =>
                  onChange({
                    is_active:
                      e.target.value === ''
                        ? undefined
                        : e.target.value === 'active',
                  })
                }
                className="
                  w-full rounded-sm border border-border bg-background
                  px-3 py-2 font-mono text-sm text-foreground
                  focus:border-brand-primary focus:outline-none
                  focus:ring-2 focus:ring-brand-primary focus:ring-offset-0
                "
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            {/* Lock Status Filter */}
            <div>
              <label className="mb-2 block font-mono text-xs text-foreground">
                Lock Status
              </label>
              <select
                value={
                  filters.is_locked === undefined
                    ? ''
                    : filters.is_locked
                    ? 'locked'
                    : 'unlocked'
                }
                onChange={(e) =>
                  onChange({
                    is_locked:
                      e.target.value === ''
                        ? undefined
                        : e.target.value === 'locked',
                  })
                }
                className="
                  w-full rounded-sm border border-border bg-background
                  px-3 py-2 font-mono text-sm text-foreground
                  focus:border-brand-primary focus:outline-none
                  focus:ring-2 focus:ring-brand-primary focus:ring-offset-0
                "
              >
                <option value="">All</option>
                <option value="unlocked">Unlocked</option>
                <option value="locked">Locked</option>
              </select>
            </div>
          </div>
        </div>
      )}

      {/* Active Filters Display */}
      {activeFilterCount > 0 && (
        <div className="flex flex-wrap gap-2">
          {filters.search && (
            <Badge variant="primary" size="md">
              Search: {filters.search}
              <button
                onClick={() => onChange({ search: undefined })}
                className="ml-2 hover:text-foreground"
              >
                <X className="h-3 w-3" />
              </button>
            </Badge>
          )}
          {filters.role && (
            <Badge variant="primary" size="md">
              Role: {roles.find((r) => r.value === filters.role)?.label}
              <button
                onClick={() => onChange({ role: undefined })}
                className="ml-2 hover:text-foreground"
              >
                <X className="h-3 w-3" />
              </button>
            </Badge>
          )}
          {filters.is_active !== undefined && (
            <Badge variant="primary" size="md">
              {filters.is_active ? 'Active' : 'Inactive'}
              <button
                onClick={() => onChange({ is_active: undefined })}
                className="ml-2 hover:text-foreground"
              >
                <X className="h-3 w-3" />
              </button>
            </Badge>
          )}
          {filters.is_locked !== undefined && (
            <Badge variant="primary" size="md">
              {filters.is_locked ? 'Locked' : 'Unlocked'}
              <button
                onClick={() => onChange({ is_locked: undefined })}
                className="ml-2 hover:text-foreground"
              >
                <X className="h-3 w-3" />
              </button>
            </Badge>
          )}
        </div>
      )}
    </div>
  );
}
```

---

### 1.4 Composants UI de Base (Monologue)

**Fichier** : `resources/js/components/ui/Badge.tsx`

```typescript
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
  'inline-flex items-center rounded-sm font-mono transition-colors',
  {
    variants: {
      variant: {
        default: 'bg-background-elevated text-foreground border border-border',
        primary: 'bg-brand-primary/20 text-brand-primary border border-brand-primary/30',
        accent: 'bg-brand-accent/20 text-brand-accent border border-brand-accent/30',
        success: 'bg-brand-success/20 text-brand-success border border-brand-success/30',
        muted: 'bg-background-muted/30 text-foreground-muted border border-border-muted',
      },
      size: {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-sm',
        lg: 'px-3 py-1.5 text-sm',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  }
);

export interface BadgeProps
  extends React.HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof badgeVariants> {}

export function Badge({ className, variant, size, ...props }: BadgeProps) {
  return (
    <span className={cn(badgeVariants({ variant, size }), className)} {...props} />
  );
}
```

---

**Fichier** : `resources/js/components/ui/Button.tsx`

```typescript
import { forwardRef } from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';
import { Loader2 } from 'lucide-react';

const buttonVariants = cva(
  `
    inline-flex items-center justify-center rounded-sm font-mono
    transition-colors focus-visible:outline-none focus-visible:ring-2
    focus-visible:ring-brand-primary focus-visible:ring-offset-0
    disabled:pointer-events-none disabled:opacity-50
  `,
  {
    variants: {
      variant: {
        primary: 'bg-brand-primary text-neutral-900 hover:bg-brand-accent',
        secondary: 'bg-background-elevated text-foreground border border-border hover:bg-background-muted',
        ghost: 'text-foreground-secondary hover:bg-background-elevated hover:text-foreground',
        link: 'text-brand-primary underline-offset-4 hover:text-brand-accent hover:underline',
      },
      size: {
        sm: 'h-8 px-3 text-xs',
        md: 'h-10 px-4 text-sm',
        lg: 'h-12 px-6 text-base',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  loading?: boolean;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant,
      size,
      loading = false,
      leftIcon,
      rightIcon,
      children,
      disabled,
      ...props
    },
    ref
  ) => {
    return (
      <button
        ref={ref}
        disabled={disabled || loading}
        className={cn(buttonVariants({ variant, size }), className)}
        {...props}
      >
        {loading ? (
          <>
            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
            {children}
          </>
        ) : (
          <>
            {leftIcon && <span className="mr-2">{leftIcon}</span>}
            {children}
            {rightIcon && <span className="ml-2">{rightIcon}</span>}
          </>
        )}
      </button>
    );
  }
);

Button.displayName = 'Button';
```

---

**Fichier** : `resources/js/components/ui/Input.tsx`

```typescript
import { forwardRef } from 'react';
import { cn } from '@/lib/utils';

export interface InputProps
  extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helpText?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  multiline?: boolean;
  rows?: number;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      className,
      label,
      error,
      helpText,
      leftIcon,
      rightIcon,
      multiline = false,
      rows = 3,
      type = 'text',
      ...props
    },
    ref
  ) => {
    const inputClasses = cn(
      `
        w-full rounded-sm border bg-background px-3 py-2.5
        font-mono text-sm text-foreground
        placeholder:text-foreground-muted
        focus:outline-none focus:ring-2 focus:ring-offset-0
        disabled:cursor-not-allowed disabled:opacity-50
      `,
      error
        ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
        : 'border-border focus:border-brand-primary focus:ring-brand-primary',
      leftIcon && 'pl-10',
      rightIcon && 'pr-10',
      className
    );

    return (
      <div className="w-full">
        {label && (
          <label className="mb-2 block font-mono text-xs text-foreground">
            {label}
            {props.required && (
              <span className="ml-1 text-brand-primary">*</span>
            )}
          </label>
        )}

        <div className="relative">
          {leftIcon && (
            <div className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-foreground-muted">
              {leftIcon}
            </div>
          )}

          {multiline ? (
            <textarea
              className={inputClasses}
              rows={rows}
              {...(props as any)}
            />
          ) : (
            <input ref={ref} type={type} className={inputClasses} {...props} />
          )}

          {rightIcon && (
            <div className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-foreground-muted">
              {rightIcon}
            </div>
          )}
        </div>

        {error && (
          <p className="mt-1 font-mono text-xs text-red-400">{error}</p>
        )}

        {helpText && !error && (
          <p className="mt-1 font-mono text-xs text-foreground-muted">
            {helpText}
          </p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';
```

---

## 🧪 II. TESTS (Vitest + Playwright)

### 2.1 Tests Unitaires (Vitest)

**Fichier** : `tests/unit/Services/UserManagementService.test.ts`

```typescript
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { UserManagementService } from '@/Services/UserManagementService';
import { User } from '@/Models/User';
import { UserActivityLog } from '@/Models/UserActivityLog';

describe('UserManagementService', () => {
  let service: UserManagementService;

  beforeEach(() => {
    service = new UserManagementService();
  });

  describe('createUser', () => {
    it('should create a user with generated password if not provided', async () => {
      const userData = {
        name: 'John Doe',
        email: 'john@example.com',
        role: 'user',
      };

      const user = await service.createUser(userData);

      expect(user.name).toBe('John Doe');
      expect(user.email).toBe('john@example.com');
      expect(user.role).toBe('user');
      expect(user.password).toBeDefined();
    });

    it('should log user creation activity', async () => {
      const userData = {
        name: 'John Doe',
        email: 'john@example.com',
        role: 'user',
      };

      const performedBy = { id: 1, name: 'Admin' } as User;

      await service.createUser(userData, performedBy);

      const logs = await UserActivityLog.where('action', 'created').get();
      expect(logs.length).toBeGreaterThan(0);
      expect(logs[0].performed_by).toBe(performedBy.id);
    });
  });

  describe('generateCredentials', () => {
    it('should generate secure password', async () => {
      const user = await User.create({
        name: 'Test User',
        email: 'test@example.com',
        role: 'user',
      });

      const credentials = await service.generateCredentials(user);

      expect(credentials.password).toBeDefined();
      expect(credentials.password.length).toBeGreaterThanOrEqual(16);
      expect(credentials.api_token).toBeDefined();
      expect(credentials.basic_auth).toBeDefined();
    });

    it('should generate valid Base64 Basic Auth', async () => {
      const user = await User.create({
        name: 'Test User',
        email: 'test@example.com',
        role: 'user',
      });

      const credentials = await service.generateCredentials(user);

      // Verify Base64 format
      expect(() => atob(credentials.basic_auth)).not.toThrow();

      // Verify content
      const decoded = atob(credentials.basic_auth);
      expect(decoded).toContain(user.email);
      expect(decoded).toContain(':');
    });

    it('should contain uppercase, lowercase, numbers, and special chars', async () => {
      const password = service.generateSecurePassword();

      expect(/[A-Z]/.test(password)).toBe(true);
      expect(/[a-z]/.test(password)).toBe(true);
      expect(/[0-9]/.test(password)).toBe(true);
      expect(/[!@#$%^&*()\-_=+[\]{}]/.test(password)).toBe(true);
    });
  });

  describe('changeRole', () => {
    it('should change user role and log the change', async () => {
      const user = await User.create({
        name: 'Test User',
        email: 'test@example.com',
        role: 'user',
      });

      const admin = { id: 1, name: 'Admin' } as User;

      await service.changeRole(user, 'manager', admin);

      const updated = await User.find(user.id);
      expect(updated.role).toBe('manager');

      const logs = await UserActivityLog.where('action', 'role_changed')
        .where('user_id', user.id)
        .get();

      expect(logs.length).toBe(1);
      expect(logs[0].old_values.role).toBe('user');
      expect(logs[0].new_values.role).toBe('manager');
    });
  });

  describe('updatePermissions', () => {
    it('should update custom permissions', async () => {
      const user = await User.create({
        name: 'Test User',
        email: 'test@example.com',
        role: 'user',
        permissions: ['integrations.view'],
      });

      const newPermissions = ['integrations.view', 'integrations.edit'];

      await service.updatePermissions(user, newPermissions);

      const updated = await User.find(user.id);
      expect(updated.permissions).toEqual(newPermissions);
    });
  });
});
```

---

### 2.2 Tests E2E (Playwright)

**Fichier** : `tests/e2e/admin/user-management.spec.ts`

```typescript
import { test, expect } from '@playwright/test';

test.describe('User Management', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('[name="email"]', 'admin@example.com');
    await page.fill('[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/dashboard');

    // Navigate to user management
    await page.goto('/admin/users');
  });

  test('should display user list', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('User Management');
    await expect(page.locator('table')).toBeVisible();

    // Should have at least the admin user
    const rows = page.locator('tbody tr');
    await expect(rows).not.toHaveCount(0);
  });

  test('should filter users by search', async ({ page }) => {
    // Type in search box
    await page.fill('[placeholder*="Search"]', 'admin');

    // Wait for results
    await page.waitForTimeout(500);

    // Check filtered results
    const rows = page.locator('tbody tr');
    const count = await rows.count();

    for (let i = 0; i < count; i++) {
      const text = await rows.nth(i).textContent();
      expect(text?.toLowerCase()).toContain('admin');
    }
  });

  test('should create a new user', async ({ page }) => {
    // Click Add User button
    await page.click('text=Add User');
    await expect(page).toHaveURL('/admin/users/create');

    // Fill form
    await page.fill('[name="name"]', 'Test User');
    await page.fill('[name="email"]', 'test@example.com');

    // Generate password
    await page.click('text=Generate');
    await page.waitForTimeout(300);

    // Select role
    await page.click('[role="listbox"]');
    await page.click('text=User');

    // Submit
    await page.click('button[type="submit"]:has-text("Create User")');

    // Should redirect to list
    await expect(page).toHaveURL('/admin/users');

    // Should show success message (assuming toast/notification)
    await expect(page.locator('text=User created successfully')).toBeVisible();

    // Should see new user in list
    await expect(page.locator('text=test@example.com')).toBeVisible();
  });

  test('should generate credentials', async ({ page }) => {
    // Find a user row (not the current admin)
    await page.click('tbody tr:nth-child(2) button:has-text("⋮")');

    // Click Generate Credentials
    await page.click('text=Generate Credentials');

    // Should show credential dialog/modal
    await expect(page.locator('text=Password')).toBeVisible();
    await expect(page.locator('text=API Token')).toBeVisible();
    await expect(page.locator('text=Basic Auth')).toBeVisible();

    // Should show curl example
    await expect(page.locator('text=curl Example')).toBeVisible();

    // Test copy button
    await page.click('button:has-text("Copy"):first');

    // Note: Can't actually test clipboard in Playwright without permissions
    // but we can verify the button exists and is clickable
  });

  test('should lock and unlock user', async ({ page }) => {
    // Find a user row
    const row = page.locator('tbody tr:nth-child(2)');
    await row.locator('button:has-text("⋮")').click();

    // Lock the user
    await page.click('text=Lock Account');

    // Should show locked badge
    await expect(row.locator('text=Locked')).toBeVisible();

    // Unlock
    await row.locator('button:has-text("⋮")').click();
    await page.click('text=Unlock Account');

    // Should remove locked badge
    await expect(row.locator('text=Locked')).not.toBeVisible();
  });

  test('should change user role', async ({ page }) => {
    // Go to edit page
    const row = page.locator('tbody tr:nth-child(2)');
    await row.locator('button:has-text("⋮")').click();
    await page.click('text=Edit');

    // Change role
    await page.click('[role="listbox"]');
    await page.click('text=Manager');

    // Save
    await page.click('button[type="submit"]:has-text("Save")');

    // Should show updated role
    await expect(page.locator('text=Manager')).toBeVisible();
  });

  test('should manage permissions', async ({ page }) => {
    // Go to edit page
    const row = page.locator('tbody tr:nth-child(2)');
    await row.locator('button:has-text("⋮")').click();
    await page.click('text=Edit');

    // Expand a permission category
    await page.click('text=Users');

    // Toggle a permission
    const checkbox = page.locator('[type="checkbox"]:has-text("View Users")');
    await checkbox.check();

    // Save
    await page.click('button[type="submit"]:has-text("Save")');

    // Verify saved
    await expect(page.locator('text=Permissions updated successfully')).toBeVisible();
  });

  test('should delete user with confirmation', async ({ page }) => {
    // Get initial row count
    const initialCount = await page.locator('tbody tr').count();

    // Click delete
    await page.locator('tbody tr:last-child button:has-text("⋮")').click();
    await page.click('text=Delete User');

    // Confirm dialog
    page.on('dialog', (dialog) => dialog.accept());
    await page.waitForTimeout(500);

    // Should have one less row
    const newCount = await page.locator('tbody tr').count();
    expect(newCount).toBe(initialCount - 1);
  });

  test('should prevent self-deletion', async ({ page }) => {
    // Try to delete current admin (first row)
    await page.locator('tbody tr:first-child button:has-text("⋮")').click();
    await page.click('text=Delete User');

    // Should show error
    await expect(
      page.locator('text=You cannot delete your own account')
    ).toBeVisible();
  });
});
```

---

**[SUITE DANS PROCHAIN MESSAGE - Partie III & IV & V...]**

---

**Dernière mise à jour** : 2025-11-02
**Tests** : Vitest (unit) + Playwright (E2E)