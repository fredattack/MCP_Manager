# 🗺️ Roadmap Part 2 - Frontend React avec Design System Monologue

> **Continuation de** : ADMIN_PANEL_ROADMAP.md
> **Design System** : Monologue (dark-themed, minimalist, typography-forward)
> **Stack Frontend** : React 19 + TypeScript + TailwindCSS 4 + Inertia.js

---

## 🎨 I. DESIGN SYSTEM MONOLOGUE - RAPPEL

### Philosophie Design

- **Minimaliste** : Focus sur le contenu, pas la décoration
- **Dark-themed** : Background noir (#010101) avec texte blanc
- **Typography-forward** : Instrument Serif (titres) + DM Mono (corps)
- **Monochrome** : Palette neutre avec accent cyan vibrant (#19d0e8)
- **Pas de shadows** : Backgrounds en couches, élévation par couleur
- **Accessible** : WCAG 2.1 Level AA compliant

### Tokens de Design

```typescript
// Colors
const colors = {
  brand: {
    primary: '#19d0e8',    // Cyan accent
    accent: '#44ccff',     // Bright blue
    success: '#a6ee98',    // Light green
  },
  neutral: {
    900: '#010101',        // Primary background
    800: '#141414',        // Secondary background
    700: '#282828',        // Elevated surfaces
    white: '#ffffff',      // Primary text
  },
  foreground: {
    DEFAULT: '#ffffff',                    // Primary text
    secondary: 'rgba(255, 255, 255, 0.64)', // Secondary text
    muted: 'rgba(255, 255, 255, 0.48)',     // Muted text
  },
  border: {
    DEFAULT: 'rgba(255, 255, 255, 0.12)',  // Default border
  },
};

// Typography
const fontFamily = {
  serif: '"Instrument Serif", serif',     // Headings
  mono: '"DM Mono", monospace',           // Body, UI
};

// Spacing (Monologue custom scale)
const spacing = {
  0: '0px',
  1: '10px',
  2: '14px',
  3: '16px',
  4: '18px',
  5: '20px',
  6: '40px',
  7: '154px',
};

// Border Radius
const borderRadius = {
  sm: '6px',
  DEFAULT: '6px',
  md: '8px',
};
```

---

## 🧩 II. PHASE 3 - INTERFACE REACT (2 jours)

### 3.1 Pages Admin

#### Page : Liste des Utilisateurs

**Fichier** : `resources/js/Pages/Admin/Users/Index.tsx`

```typescript
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { UserTable } from '@/components/admin/UserTable';
import { UserFilters } from '@/components/admin/UserFilters';
import { Button } from '@/components/ui/Button';
import { Plus, Download } from 'lucide-react';
import type { User, UserRole } from '@/types';

interface Props {
  users: {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
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
    description: string;
  }>;
  can: {
    create: boolean;
    edit: boolean;
    delete: boolean;
  };
}

export default function UsersIndex({ users, filters, roles, can }: Props) {
  const [selectedUsers, setSelectedUsers] = useState<number[]>([]);

  const handleFilterChange = (newFilters: Partial<typeof filters>) => {
    router.get(
      route('admin.users.index'),
      { ...filters, ...newFilters },
      { preserveState: true, preserveScroll: true }
    );
  };

  const handleSort = (column: string) => {
    const newOrder =
      filters.sort_by === column && filters.sort_order === 'asc'
        ? 'desc'
        : 'asc';

    handleFilterChange({ sort_by: column, sort_order: newOrder });
  };

  const handleExport = () => {
    window.location.href = route('admin.users.export', filters);
  };

  return (
    <AuthenticatedLayout>
      <Head title="User Management" />

      {/* Header */}
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h1 className="font-serif text-lg text-foreground">
            User Management
          </h1>
          <p className="mt-1 font-mono text-sm text-foreground-secondary">
            Manage users, roles, and permissions
          </p>
        </div>

        <div className="flex gap-2">
          <Button
            variant="ghost"
            size="md"
            onClick={handleExport}
            leftIcon={<Download className="h-4 w-4" />}
          >
            Export
          </Button>

          {can.create && (
            <Button
              variant="primary"
              size="md"
              onClick={() => router.visit(route('admin.users.create'))}
              leftIcon={<Plus className="h-4 w-4" />}
            >
              Add User
            </Button>
          )}
        </div>
      </div>

      {/* Filters */}
      <UserFilters
        filters={filters}
        roles={roles}
        onChange={handleFilterChange}
      />

      {/* Stats */}
      <div className="mb-6 grid grid-cols-4 gap-3">
        <div className="rounded-md border border-border bg-background-secondary p-4">
          <p className="font-mono text-xs text-foreground-muted">Total Users</p>
          <p className="mt-1 font-mono text-lg text-foreground">{users.total}</p>
        </div>
        <div className="rounded-md border border-border bg-background-secondary p-4">
          <p className="font-mono text-xs text-foreground-muted">Active</p>
          <p className="mt-1 font-mono text-lg text-brand-success">
            {users.data.filter((u) => u.is_active).length}
          </p>
        </div>
        <div className="rounded-md border border-border bg-background-secondary p-4">
          <p className="font-mono text-xs text-foreground-muted">Locked</p>
          <p className="mt-1 font-mono text-lg text-foreground-secondary">
            {users.data.filter((u) => u.is_locked).length}
          </p>
        </div>
        <div className="rounded-md border border-border bg-background-secondary p-4">
          <p className="font-mono text-xs text-foreground-muted">Admins</p>
          <p className="mt-1 font-mono text-lg text-brand-primary">
            {users.data.filter((u) => u.role === 'admin').length}
          </p>
        </div>
      </div>

      {/* Table */}
      <UserTable
        users={users.data}
        selectedUsers={selectedUsers}
        onSelectionChange={setSelectedUsers}
        onSort={handleSort}
        sortBy={filters.sort_by}
        sortOrder={filters.sort_order}
        can={can}
      />

      {/* Pagination */}
      {users.last_page > 1 && (
        <div className="mt-6 flex items-center justify-between border-t border-border pt-6">
          <p className="font-mono text-sm text-foreground-secondary">
            Showing {users.data.length} of {users.total} users
          </p>

          <div className="flex gap-2">
            {Array.from({ length: users.last_page }, (_, i) => i + 1).map(
              (page) => (
                <button
                  key={page}
                  onClick={() => handleFilterChange({ page })}
                  className={`
                    rounded-sm px-3 py-1.5 font-mono text-sm transition-colors
                    ${
                      page === users.current_page
                        ? 'bg-brand-primary text-neutral-900'
                        : 'bg-background-secondary text-foreground-secondary hover:bg-background-elevated hover:text-foreground'
                    }
                  `}
                >
                  {page}
                </button>
              )
            )}
          </div>
        </div>
      )}
    </AuthenticatedLayout>
  );
}
```

---

#### Page : Créer un Utilisateur

**Fichier** : `resources/js/Pages/Admin/Users/Create.tsx`

```typescript
import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { RoleSelector } from '@/components/admin/RoleSelector';
import { PermissionManager } from '@/components/admin/PermissionManager';
import { ArrowLeft, Save } from 'lucide-react';
import type { UserRole } from '@/types';

interface Props {
  roles: Array<{
    value: string;
    label: string;
    description: string;
  }>;
}

export default function CreateUser({ roles }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
    password: '',
    role: 'user' as UserRole,
    permissions: [] as string[],
    is_active: true,
    notes: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.users.store'), {
      onSuccess: () => {
        router.visit(route('admin.users.index'));
      },
    });
  };

  const handleGeneratePassword = () => {
    const password = generateSecurePassword();
    setData('password', password);

    // Show password in modal or toast
    alert(`Generated password: ${password}\n\nPlease save this password securely.`);
  };

  return (
    <AuthenticatedLayout>
      <Head title="Create User" />

      {/* Header */}
      <div className="mb-6 flex items-center gap-4">
        <Button
          variant="ghost"
          size="md"
          onClick={() => router.visit(route('admin.users.index'))}
          leftIcon={<ArrowLeft className="h-4 w-4" />}
        >
          Back
        </Button>

        <div>
          <h1 className="font-serif text-lg text-foreground">Create User</h1>
          <p className="mt-1 font-mono text-sm text-foreground-secondary">
            Add a new user to the system
          </p>
        </div>
      </div>

      {/* Form */}
      <form onSubmit={handleSubmit} className="max-w-2xl space-y-6">
        {/* Basic Info Card */}
        <div className="rounded-md border border-border bg-background-secondary p-6">
          <h2 className="mb-4 font-mono text-sm text-foreground">
            Basic Information
          </h2>

          <div className="space-y-4">
            <Input
              label="Full Name"
              value={data.name}
              onChange={(e) => setData('name', e.target.value)}
              error={errors.name}
              required
              placeholder="John Doe"
            />

            <Input
              label="Email Address"
              type="email"
              value={data.email}
              onChange={(e) => setData('email', e.target.value)}
              error={errors.email}
              required
              placeholder="john@example.com"
            />

            <div className="flex gap-3">
              <Input
                label="Password"
                type="password"
                value={data.password}
                onChange={(e) => setData('password', e.target.value)}
                error={errors.password}
                placeholder="Leave empty to auto-generate"
                className="flex-1"
              />
              <div className="pt-6">
                <Button
                  type="button"
                  variant="secondary"
                  size="md"
                  onClick={handleGeneratePassword}
                >
                  Generate
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Role & Permissions Card */}
        <div className="rounded-md border border-border bg-background-secondary p-6">
          <h2 className="mb-4 font-mono text-sm text-foreground">
            Role & Permissions
          </h2>

          <div className="space-y-4">
            <RoleSelector
              value={data.role}
              onChange={(role) => setData('role', role)}
              roles={roles}
              error={errors.role}
            />

            <PermissionManager
              role={data.role}
              permissions={data.permissions}
              onChange={(permissions) => setData('permissions', permissions)}
            />
          </div>
        </div>

        {/* Account Status Card */}
        <div className="rounded-md border border-border bg-background-secondary p-6">
          <h2 className="mb-4 font-mono text-sm text-foreground">
            Account Status
          </h2>

          <label className="flex items-center gap-3">
            <input
              type="checkbox"
              checked={data.is_active}
              onChange={(e) => setData('is_active', e.target.checked)}
              className="h-4 w-4 rounded-sm border-border bg-background text-brand-primary focus:ring-brand-primary focus:ring-offset-0"
            />
            <span className="font-mono text-sm text-foreground">
              Account is active
            </span>
          </label>

          <Input
            label="Notes (optional)"
            value={data.notes}
            onChange={(e) => setData('notes', e.target.value)}
            error={errors.notes}
            placeholder="Internal notes about this user..."
            className="mt-4"
            multiline
            rows={3}
          />
        </div>

        {/* Actions */}
        <div className="flex justify-end gap-3 border-t border-border pt-6">
          <Button
            type="button"
            variant="ghost"
            size="md"
            onClick={() => router.visit(route('admin.users.index'))}
            disabled={processing}
          >
            Cancel
          </Button>

          <Button
            type="submit"
            variant="primary"
            size="md"
            loading={processing}
            leftIcon={<Save className="h-4 w-4" />}
          >
            Create User
          </Button>
        </div>
      </form>
    </AuthenticatedLayout>
  );
}

// Helper function
function generateSecurePassword(length = 16): string {
  const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const lowercase = 'abcdefghijklmnopqrstuvwxyz';
  const numbers = '0123456789';
  const special = '!@#$%^&*()-_=+[]{}';
  const all = uppercase + lowercase + numbers + special;

  let password = '';
  password += uppercase[Math.floor(Math.random() * uppercase.length)];
  password += lowercase[Math.floor(Math.random() * lowercase.length)];
  password += numbers[Math.floor(Math.random() * numbers.length)];
  password += special[Math.floor(Math.random() * special.length)];

  for (let i = 4; i < length; i++) {
    password += all[Math.floor(Math.random() * all.length)];
  }

  return password
    .split('')
    .sort(() => Math.random() - 0.5)
    .join('');
}
```

---

### 3.2 Composants UI (Conformes Monologue)

#### Composant : UserTable

**Fichier** : `resources/js/components/admin/UserTable.tsx`

```typescript
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/DropdownMenu';
import {
  MoreHorizontal,
  Edit,
  Key,
  Lock,
  Unlock,
  Trash2,
  Eye,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import type { User } from '@/types';

interface UserTableProps {
  users: User[];
  selectedUsers: number[];
  onSelectionChange: (ids: number[]) => void;
  onSort: (column: string) => void;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
  can: {
    edit: boolean;
    delete: boolean;
  };
}

export function UserTable({
  users,
  selectedUsers,
  onSelectionChange,
  onSort,
  sortBy,
  sortOrder,
  can,
}: UserTableProps) {
  const [hoveredRow, setHoveredRow] = useState<number | null>(null);

  const handleSelectAll = (checked: boolean) => {
    onSelectionChange(checked ? users.map((u) => u.id) : []);
  };

  const handleSelectOne = (id: number, checked: boolean) => {
    onSelectionChange(
      checked
        ? [...selectedUsers, id]
        : selectedUsers.filter((uid) => uid !== id)
    );
  };

  const getRoleBadgeVariant = (role: string) => {
    switch (role) {
      case 'admin':
        return 'primary';
      case 'manager':
        return 'accent';
      case 'user':
        return 'default';
      case 'read_only':
        return 'muted';
      default:
        return 'default';
    }
  };

  return (
    <div className="overflow-hidden rounded-md border border-border bg-background-secondary">
      <table className="w-full">
        <thead>
          <tr className="border-b border-border bg-background-elevated">
            <th className="w-12 p-3">
              <input
                type="checkbox"
                checked={
                  users.length > 0 && selectedUsers.length === users.length
                }
                onChange={(e) => handleSelectAll(e.target.checked)}
                className="h-4 w-4 rounded-sm border-border bg-background text-brand-primary focus:ring-brand-primary focus:ring-offset-0"
              />
            </th>
            <SortableHeader
              label="Name"
              column="name"
              sortBy={sortBy}
              sortOrder={sortOrder}
              onSort={onSort}
            />
            <SortableHeader
              label="Email"
              column="email"
              sortBy={sortBy}
              sortOrder={sortOrder}
              onSort={onSort}
            />
            <SortableHeader
              label="Role"
              column="role"
              sortBy={sortBy}
              sortOrder={sortOrder}
              onSort={onSort}
            />
            <th className="p-3 text-left font-mono text-xs text-foreground-muted">
              Status
            </th>
            <SortableHeader
              label="Last Login"
              column="last_login_at"
              sortBy={sortBy}
              sortOrder={sortOrder}
              onSort={onSort}
            />
            <th className="w-16 p-3"></th>
          </tr>
        </thead>
        <tbody>
          {users.length === 0 ? (
            <tr>
              <td colSpan={7} className="p-6 text-center">
                <p className="font-mono text-sm text-foreground-muted">
                  No users found
                </p>
              </td>
            </tr>
          ) : (
            users.map((user) => (
              <tr
                key={user.id}
                onMouseEnter={() => setHoveredRow(user.id)}
                onMouseLeave={() => setHoveredRow(null)}
                className={`
                  border-b border-border transition-colors
                  ${
                    hoveredRow === user.id
                      ? 'bg-background-elevated'
                      : 'bg-background-secondary'
                  }
                `}
              >
                <td className="p-3">
                  <input
                    type="checkbox"
                    checked={selectedUsers.includes(user.id)}
                    onChange={(e) => handleSelectOne(user.id, e.target.checked)}
                    className="h-4 w-4 rounded-sm border-border bg-background text-brand-primary focus:ring-brand-primary focus:ring-offset-0"
                  />
                </td>
                <td className="p-3">
                  <div className="flex items-center gap-3">
                    <div className="flex h-8 w-8 items-center justify-center rounded-sm bg-background-elevated font-mono text-sm text-foreground">
                      {user.name.charAt(0).toUpperCase()}
                    </div>
                    <span className="font-mono text-sm text-foreground">
                      {user.name}
                    </span>
                  </div>
                </td>
                <td className="p-3">
                  <span className="font-mono text-sm text-foreground-secondary">
                    {user.email}
                  </span>
                </td>
                <td className="p-3">
                  <Badge variant={getRoleBadgeVariant(user.role)} size="sm">
                    {user.role.replace('_', ' ')}
                  </Badge>
                </td>
                <td className="p-3">
                  <div className="flex gap-2">
                    <Badge
                      variant={user.is_active ? 'success' : 'muted'}
                      size="sm"
                    >
                      {user.is_active ? 'Active' : 'Inactive'}
                    </Badge>
                    {user.is_locked && (
                      <Badge variant="muted" size="sm">
                        Locked
                      </Badge>
                    )}
                  </div>
                </td>
                <td className="p-3">
                  <span className="font-mono text-xs text-foreground-muted">
                    {user.last_login_at
                      ? formatDistanceToNow(new Date(user.last_login_at), {
                          addSuffix: true,
                        })
                      : 'Never'}
                  </span>
                </td>
                <td className="p-3">
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="ghost" size="sm">
                        <MoreHorizontal className="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem
                        onClick={() =>
                          router.visit(route('admin.users.show', user.id))
                        }
                      >
                        <Eye className="mr-2 h-4 w-4" />
                        View Details
                      </DropdownMenuItem>

                      {can.edit && (
                        <>
                          <DropdownMenuItem
                            onClick={() =>
                              router.visit(route('admin.users.edit', user.id))
                            }
                          >
                            <Edit className="mr-2 h-4 w-4" />
                            Edit
                          </DropdownMenuItem>

                          <DropdownMenuItem
                            onClick={() =>
                              router.post(
                                route('admin.users.generate-credentials', user.id)
                              )
                            }
                          >
                            <Key className="mr-2 h-4 w-4" />
                            Generate Credentials
                          </DropdownMenuItem>

                          {user.is_locked ? (
                            <DropdownMenuItem
                              onClick={() =>
                                router.post(route('admin.users.unlock', user.id))
                              }
                            >
                              <Unlock className="mr-2 h-4 w-4" />
                              Unlock Account
                            </DropdownMenuItem>
                          ) : (
                            <DropdownMenuItem
                              onClick={() =>
                                router.post(route('admin.users.lock', user.id), {
                                  reason: 'Manually locked by admin',
                                })
                              }
                            >
                              <Lock className="mr-2 h-4 w-4" />
                              Lock Account
                            </DropdownMenuItem>
                          )}
                        </>
                      )}

                      {can.delete && (
                        <>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem
                            onClick={() => {
                              if (
                                confirm(
                                  `Are you sure you want to delete ${user.name}?`
                                )
                              ) {
                                router.delete(route('admin.users.destroy', user.id));
                              }
                            }}
                            className="text-red-400 focus:text-red-300"
                          >
                            <Trash2 className="mr-2 h-4 w-4" />
                            Delete User
                          </DropdownMenuItem>
                        </>
                      )}
                    </DropdownMenuContent>
                  </DropdownMenu>
                </td>
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  );
}

// Helper component
function SortableHeader({
  label,
  column,
  sortBy,
  sortOrder,
  onSort,
}: {
  label: string;
  column: string;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
  onSort: (column: string) => void;
}) {
  const isActive = sortBy === column;

  return (
    <th className="p-3 text-left">
      <button
        onClick={() => onSort(column)}
        className="flex items-center gap-2 font-mono text-xs text-foreground-muted transition-colors hover:text-foreground"
      >
        {label}
        {isActive && (
          <span className="text-brand-primary">
            {sortOrder === 'asc' ? '↑' : '↓'}
          </span>
        )}
      </button>
    </th>
  );
}
```

---

#### Composant : CredentialGenerator

**Fichier** : `resources/js/components/admin/CredentialGenerator.tsx`

```typescript
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { Badge } from '@/components/ui/Badge';
import {
  RefreshCw,
  Copy,
  Check,
  Eye,
  EyeOff,
  Key,
  Terminal,
} from 'lucide-react';
import type { User } from '@/types';

interface CredentialGeneratorProps {
  user: User;
  onGenerate?: (credentials: Credentials) => void;
}

interface Credentials {
  password: string;
  api_token: string;
  basic_auth: string;
  basic_auth_header: string;
}

export function CredentialGenerator({
  user,
  onGenerate,
}: CredentialGeneratorProps) {
  const [credentials, setCredentials] = useState<Credentials | null>(null);
  const [loading, setLoading] = useState(false);
  const [copied, setCopied] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);

  const handleGenerate = async () => {
    setLoading(true);

    router.post(
      route('admin.users.generate-credentials', user.id),
      {},
      {
        onSuccess: (page) => {
          const creds = page.props.credentials as Credentials;
          setCredentials(creds);
          onGenerate?.(creds);
        },
        onFinish: () => setLoading(false),
      }
    );
  };

  const handleCopy = (text: string, key: string) => {
    navigator.clipboard.writeText(text);
    setCopied(key);
    setTimeout(() => setCopied(null), 2000);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div>
          <h3 className="font-mono text-sm text-foreground">
            Credential Generator
          </h3>
          <p className="mt-1 font-mono text-xs text-foreground-muted">
            Generate secure credentials for <span className="text-brand-primary">{user.email}</span>
          </p>
        </div>

        <Button
          variant="primary"
          size="md"
          onClick={handleGenerate}
          loading={loading}
          leftIcon={<RefreshCw className="h-4 w-4" />}
        >
          Generate New
        </Button>
      </div>

      {/* Warning */}
      {!credentials && (
        <div className="rounded-md border border-border bg-background-elevated p-4">
          <p className="font-mono text-xs text-foreground-secondary">
            ⚠️ Generated credentials will be shown only once. Make sure to save
            them securely before closing this window.
          </p>
        </div>
      )}

      {/* Credentials Display */}
      {credentials && (
        <div className="space-y-4">
          {/* Password */}
          <CredentialField
            label="Password"
            value={credentials.password}
            secret={!showPassword}
            onCopy={() => handleCopy(credentials.password, 'password')}
            copied={copied === 'password'}
            icon={<Key className="h-4 w-4" />}
            actions={
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setShowPassword(!showPassword)}
              >
                {showPassword ? (
                  <EyeOff className="h-4 w-4" />
                ) : (
                  <Eye className="h-4 w-4" />
                )}
              </Button>
            }
          />

          {/* API Token */}
          <CredentialField
            label="API Token"
            value={credentials.api_token}
            secret
            onCopy={() => handleCopy(credentials.api_token, 'api_token')}
            copied={copied === 'api_token'}
            icon={<Key className="h-4 w-4" />}
          />

          {/* Basic Auth (Base64) */}
          <CredentialField
            label="Basic Auth (Base64 Encoded)"
            value={credentials.basic_auth}
            onCopy={() => handleCopy(credentials.basic_auth, 'basic_auth')}
            copied={copied === 'basic_auth'}
            icon={<Terminal className="h-4 w-4" />}
            badge={
              <Badge variant="accent" size="sm">
                Base64
              </Badge>
            }
          />

          {/* curl Example */}
          <div className="rounded-md border border-border bg-background p-4">
            <div className="mb-2 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <Terminal className="h-4 w-4 text-brand-primary" />
                <span className="font-mono text-xs text-foreground">
                  curl Example
                </span>
              </div>
              <Button
                variant="ghost"
                size="sm"
                onClick={() =>
                  handleCopy(getCurlExample(user.email, credentials), 'curl')
                }
              >
                {copied === 'curl' ? (
                  <Check className="h-4 w-4 text-brand-success" />
                ) : (
                  <Copy className="h-4 w-4" />
                )}
              </Button>
            </div>
            <pre className="overflow-x-auto font-mono text-xs text-foreground-secondary">
              {getCurlExample(user.email, credentials)}
            </pre>
          </div>

          {/* TypeScript Example */}
          <div className="rounded-md border border-border bg-background p-4">
            <div className="mb-2 flex items-center justify-between">
              <span className="font-mono text-xs text-foreground">
                TypeScript/JavaScript Example
              </span>
              <Button
                variant="ghost"
                size="sm"
                onClick={() =>
                  handleCopy(getTsExample(credentials), 'ts')
                }
              >
                {copied === 'ts' ? (
                  <Check className="h-4 w-4 text-brand-success" />
                ) : (
                  <Copy className="h-4 w-4" />
                )}
              </Button>
            </div>
            <pre className="overflow-x-auto font-mono text-xs text-foreground-secondary">
              {getTsExample(credentials)}
            </pre>
          </div>
        </div>
      )}
    </div>
  );
}

// Helper Component
function CredentialField({
  label,
  value,
  secret = false,
  onCopy,
  copied,
  icon,
  badge,
  actions,
}: {
  label: string;
  value: string;
  secret?: boolean;
  onCopy: () => void;
  copied: boolean;
  icon?: React.ReactNode;
  badge?: React.ReactNode;
  actions?: React.ReactNode;
}) {
  return (
    <div className="rounded-md border border-border bg-background-secondary p-4">
      <div className="mb-2 flex items-center justify-between">
        <div className="flex items-center gap-2">
          {icon && <span className="text-brand-primary">{icon}</span>}
          <span className="font-mono text-xs text-foreground">{label}</span>
          {badge}
        </div>
        <div className="flex items-center gap-1">
          {actions}
          <Button variant="ghost" size="sm" onClick={onCopy}>
            {copied ? (
              <Check className="h-4 w-4 text-brand-success" />
            ) : (
              <Copy className="h-4 w-4" />
            )}
          </Button>
        </div>
      </div>
      <div className="overflow-hidden rounded-sm bg-background px-3 py-2">
        <code className="font-mono text-sm text-foreground-secondary">
          {secret ? value.replace(/./g, '•') : value}
        </code>
      </div>
    </div>
  );
}

// Helper Functions
function getCurlExample(email: string, credentials: Credentials): string {
  return `curl -X POST http://localhost:9978/mcp \\
  -H "Authorization: Basic ${credentials.basic_auth}" \\
  -H "Content-Type: application/json" \\
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "tools/list",
    "params": {}
  }'`;
}

function getTsExample(credentials: Credentials): string {
  return `const response = await fetch('http://localhost:9978/mcp', {
  method: 'POST',
  headers: {
    'Authorization': 'Basic ${credentials.basic_auth}',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    jsonrpc: '2.0',
    id: 1,
    method: 'tools/list',
    params: {},
  }),
});

const data = await response.json();`;
}
```

---

**[SUITE DU FICHIER DANS PROCHAIN MESSAGE - Trop long pour un seul message]**

Voulez-vous que je continue avec :
- Les autres composants (RoleSelector, PermissionManager, UserFilters)
- Les tests (Unit & E2E)
- La documentation utilisateur
- Les seeders de démo

---

**Dernière mise à jour** : 2025-11-02
**Design System** : Monologue v1.0
**Conformité** : WCAG 2.1 Level AA