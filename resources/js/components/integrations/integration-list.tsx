import { Filter, Search } from 'lucide-react';
import React from 'react';
import AutoSizer from 'react-virtualized-auto-sizer';
import { FixedSizeList } from 'react-window';
import { useIntegrations } from '../../hooks/use-integrations';
import { INTEGRATION_TYPES, IntegrationAccount, IntegrationStatus, IntegrationType } from '../../types/integrations';
import { Button } from '../ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../ui/dialog';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { IntegrationCardEnhanced } from './integration-card-enhanced';
import { IntegrationForm } from './integration-form';

// Debounce hook
function useDebounce<T>(value: T, delay: number): T {
    const [debouncedValue, setDebouncedValue] = React.useState<T>(value);

    React.useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
}

const VIRTUALIZATION_THRESHOLD = 20;
const ITEM_HEIGHT = 200;

export function IntegrationList() {
    const { integrations, loading, error, fetchIntegrations, createIntegration, updateIntegration, deleteIntegration } = useIntegrations();

    const [isAddDialogOpen, setIsAddDialogOpen] = React.useState(false);
    const [selectedType, setSelectedType] = React.useState<string>(IntegrationType.NOTION);
    const [searchQuery, setSearchQuery] = React.useState('');
    const [filterType, setFilterType] = React.useState<string>('all');
    const [filterStatus, setFilterStatus] = React.useState<string>('all');

    const debouncedSearch = useDebounce(searchQuery, 300);

    React.useEffect(() => {
        fetchIntegrations().catch(console.error);
    }, [fetchIntegrations]);

    // Filtered integrations based on search and filters
    const filteredIntegrations = React.useMemo(() => {
        return integrations.filter((integration) => {
            const integrationType = INTEGRATION_TYPES[integration.type];
            const matchesSearch =
                !debouncedSearch ||
                integrationType?.displayName.toLowerCase().includes(debouncedSearch.toLowerCase()) ||
                integrationType?.description.toLowerCase().includes(debouncedSearch.toLowerCase());

            const matchesType = filterType === 'all' || integration.type === filterType;
            const matchesStatus = filterStatus === 'all' || integration.status === filterStatus;

            return matchesSearch && matchesType && matchesStatus;
        });
    }, [integrations, debouncedSearch, filterType, filterStatus]);

    const shouldUseVirtualization = filteredIntegrations.length > VIRTUALIZATION_THRESHOLD;

    const handleAddIntegration = async (data: { type: string; access_token: string; meta?: Record<string, unknown> }) => {
        await createIntegration({
            type: data.type as IntegrationType,
            access_token: data.access_token,
            meta: data.meta,
        });
        setIsAddDialogOpen(false);
    };

    const handleUpdateIntegration = async (id: number, data: Partial<IntegrationAccount>) => {
        // Convert the data to match the expected type for updateIntegration
        const updateData = {
            access_token: data.access_token,
            meta: data.meta || undefined,
            status: data.status as IntegrationStatus | undefined,
        };
        await updateIntegration(id, updateData);
    };

    const handleDeleteIntegration = async (id: number) => {
        await deleteIntegration(id);
    };

    // Virtualized row renderer
    const Row = ({ index, style }: { index: number; style: React.CSSProperties }) => {
        const integration = filteredIntegrations[index];
        return (
            <div style={style} className="px-2">
                <IntegrationCardEnhanced integration={integration} onUpdate={handleUpdateIntegration} onDelete={handleDeleteIntegration} />
            </div>
        );
    };

    return (
        <div className="space-y-6">
            {/* Header with Add Button */}
            <div className="flex items-center justify-between">
                <h2 className="text-2xl font-bold">Integrations</h2>
                <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
                    <DialogTrigger asChild>
                        <Button>Add Integration</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add New Integration</DialogTitle>
                        </DialogHeader>
                        <div className="py-4">
                            <div className="mb-4 space-y-2">
                                <label htmlFor="integration-type" className="text-sm font-medium">
                                    Integration Type
                                </label>
                                <Select value={selectedType} onValueChange={setSelectedType}>
                                    <SelectTrigger id="integration-type">
                                        <SelectValue placeholder="Select integration type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.values(INTEGRATION_TYPES).map((type) => (
                                            <SelectItem key={type.value} value={type.value}>
                                                {type.displayName}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <IntegrationForm type={selectedType} onSubmit={handleAddIntegration} submitLabel="Add Integration" />
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            {/* Search and Filters */}
            <div className="flex flex-col gap-4 md:flex-row">
                <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <Input
                        type="text"
                        placeholder="Search integrations..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pl-10"
                    />
                </div>
                <div className="flex gap-2">
                    <Select value={filterType} onValueChange={setFilterType}>
                        <SelectTrigger className="w-[180px]">
                            <Filter className="mr-2 h-4 w-4" />
                            <SelectValue placeholder="All Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            {Object.values(INTEGRATION_TYPES).map((type) => (
                                <SelectItem key={type.value} value={type.value}>
                                    {type.displayName}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filterStatus} onValueChange={setFilterStatus}>
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="All Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value={IntegrationStatus.ACTIVE}>Active</SelectItem>
                            <SelectItem value={IntegrationStatus.INACTIVE}>Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            {/* Results count */}
            {!loading && filteredIntegrations.length > 0 && (
                <div className="text-sm text-gray-600 dark:text-gray-400">
                    Showing {filteredIntegrations.length} of {integrations.length} integration{integrations.length !== 1 ? 's' : ''}
                </div>
            )}

            {/* Loading State */}
            {loading && <div className="text-center">Loading integrations...</div>}

            {/* Error State */}
            {error && (
                <div className="rounded-md bg-red-50 p-4 text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    Error loading integrations: {error.message}
                </div>
            )}

            {/* Empty State */}
            {!loading && !error && integrations.length === 0 && (
                <div className="rounded-md bg-gray-50 p-8 text-center dark:bg-gray-800">
                    <h3 className="text-lg font-medium">No integrations found</h3>
                    <p className="mt-1 text-gray-500 dark:text-gray-400">Add your first integration to connect with external services.</p>
                    <Button className="mt-4" onClick={() => setIsAddDialogOpen(true)}>
                        Add Integration
                    </Button>
                </div>
            )}

            {/* No Results State */}
            {!loading && !error && integrations.length > 0 && filteredIntegrations.length === 0 && (
                <div className="rounded-md bg-gray-50 p-8 text-center dark:bg-gray-800">
                    <h3 className="text-lg font-medium">No integrations match your filters</h3>
                    <p className="mt-1 text-gray-500 dark:text-gray-400">Try adjusting your search or filters.</p>
                    <Button
                        className="mt-4"
                        variant="outline"
                        onClick={() => {
                            setSearchQuery('');
                            setFilterType('all');
                            setFilterStatus('all');
                        }}
                    >
                        Clear Filters
                    </Button>
                </div>
            )}

            {/* Integration List - Virtualized or Regular Grid */}
            {!loading && !error && filteredIntegrations.length > 0 && (
                <>
                    {shouldUseVirtualization ? (
                        <div className="h-[600px]">
                            <AutoSizer>
                                {({ height, width }: { height: number; width: number }) => (
                                    <FixedSizeList height={height} width={width} itemCount={filteredIntegrations.length} itemSize={ITEM_HEIGHT}>
                                        {Row}
                                    </FixedSizeList>
                                )}
                            </AutoSizer>
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {filteredIntegrations.map((integration) => (
                                <IntegrationCardEnhanced
                                    key={integration.id}
                                    integration={integration}
                                    onUpdate={handleUpdateIntegration}
                                    onDelete={handleDeleteIntegration}
                                />
                            ))}
                        </div>
                    )}
                </>
            )}
        </div>
    );
}
