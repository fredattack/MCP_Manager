import { MonologueBadge } from '@/components/ui/MonologueBadge';
import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

/**
 * Design System Showcase Page
 *
 * This page demonstrates the Monologue design system components alongside
 * the existing components for visual comparison and testing.
 *
 * Route: /design-system (development only)
 */
export default function DesignSystem() {
    return (
        <AppLayout>
            <Head title="Design System - Monologue Integration" />

            <div className="mx-auto max-w-7xl space-y-12 p-6">
                {/* Header */}
                <div className="space-y-4">
                    <h1 className="font-monologue-serif text-foreground text-4xl">Design System Integration</h1>
                    <p className="font-monologue-mono text-muted-foreground text-sm">
                        Monologue design system components integrated into MCP Manager. This page showcases the new components alongside existing ones
                        for comparison.
                    </p>
                </div>

                {/* Color Palette */}
                <section className="space-y-6">
                    <h2 className="font-monologue-serif text-foreground text-2xl">Color Palette</h2>
                    <div className="grid grid-cols-2 gap-6 md:grid-cols-4">
                        {/* Brand Colors */}
                        <div className="space-y-2">
                            <h3 className="font-monologue-mono text-muted-foreground text-xs font-medium uppercase">Brand Primary</h3>
                            <div className="bg-monologue-brand-primary h-20 rounded-md"></div>
                            <code className="font-monologue-mono text-muted-foreground text-xs">#19d0e8</code>
                        </div>
                        <div className="space-y-2">
                            <h3 className="font-monologue-mono text-muted-foreground text-xs font-medium uppercase">Brand Accent</h3>
                            <div className="bg-monologue-brand-accent h-20 rounded-md"></div>
                            <code className="font-monologue-mono text-muted-foreground text-xs">#44ccff</code>
                        </div>
                        <div className="space-y-2">
                            <h3 className="font-monologue-mono text-muted-foreground text-xs font-medium uppercase">Brand Success</h3>
                            <div className="bg-monologue-brand-success h-20 rounded-md"></div>
                            <code className="font-monologue-mono text-muted-foreground text-xs">#a6ee98</code>
                        </div>
                        <div className="space-y-2">
                            <h3 className="font-monologue-mono text-muted-foreground text-xs font-medium uppercase">Neutral 800</h3>
                            <div className="bg-monologue-neutral-800 h-20 rounded-md border border-white/10"></div>
                            <code className="font-monologue-mono text-muted-foreground text-xs">#141414</code>
                        </div>
                    </div>
                </section>

                {/* Typography */}
                <section className="space-y-6">
                    <h2 className="font-monologue-serif text-foreground text-2xl">Typography</h2>
                    <div className="border-border space-y-4 rounded-lg border p-6">
                        <div className="space-y-1">
                            <p className="font-monologue-serif text-4xl">Instrument Serif</p>
                            <p className="font-monologue-mono text-muted-foreground text-xs">
                                font-monologue-serif - Used for headings and display text
                            </p>
                        </div>
                        <div className="space-y-1">
                            <p className="font-monologue-mono text-base">DM Mono Regular</p>
                            <p className="font-monologue-mono text-muted-foreground text-xs">
                                font-monologue-mono - Used for body text and UI elements
                            </p>
                        </div>
                    </div>
                </section>

                {/* Buttons Comparison */}
                <section className="space-y-6">
                    <h2 className="font-monologue-serif text-foreground text-2xl">Buttons</h2>

                    <div className="grid gap-6 lg:grid-cols-2">
                        {/* Monologue Buttons */}
                        <div className="space-y-4">
                            <h3 className="font-monologue-mono text-foreground text-sm font-medium">Monologue Design System</h3>
                            <MonologueCard>
                                <MonologueCard.Body>
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Primary</p>
                                            <div className="flex flex-wrap gap-2">
                                                <MonologueButton variant="primary" size="sm">
                                                    Small
                                                </MonologueButton>
                                                <MonologueButton variant="primary" size="md">
                                                    Medium
                                                </MonologueButton>
                                                <MonologueButton variant="primary" size="lg">
                                                    Large
                                                </MonologueButton>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Secondary</p>
                                            <div className="flex flex-wrap gap-2">
                                                <MonologueButton variant="secondary" size="sm">
                                                    Small
                                                </MonologueButton>
                                                <MonologueButton variant="secondary" size="md">
                                                    Medium
                                                </MonologueButton>
                                                <MonologueButton variant="secondary" size="lg">
                                                    Large
                                                </MonologueButton>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Ghost</p>
                                            <div className="flex flex-wrap gap-2">
                                                <MonologueButton variant="ghost" size="sm">
                                                    Small
                                                </MonologueButton>
                                                <MonologueButton variant="ghost" size="md">
                                                    Medium
                                                </MonologueButton>
                                                <MonologueButton variant="ghost" size="lg">
                                                    Large
                                                </MonologueButton>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Loading State</p>
                                            <MonologueButton variant="primary" loading>
                                                Processing...
                                            </MonologueButton>
                                        </div>
                                    </div>
                                </MonologueCard.Body>
                            </MonologueCard>
                        </div>

                        {/* Existing Buttons */}
                        <div className="space-y-4">
                            <h3 className="text-foreground font-mono text-sm font-medium">Current Design System (shadcn/ui)</h3>
                            <Card className="p-6">
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <p className="text-muted-foreground text-xs">Default</p>
                                        <div className="flex flex-wrap gap-2">
                                            <Button size="sm">Small</Button>
                                            <Button size="default">Default</Button>
                                            <Button size="lg">Large</Button>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <p className="text-muted-foreground text-xs">Secondary</p>
                                        <div className="flex flex-wrap gap-2">
                                            <Button variant="secondary" size="sm">
                                                Small
                                            </Button>
                                            <Button variant="secondary" size="default">
                                                Default
                                            </Button>
                                            <Button variant="secondary" size="lg">
                                                Large
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <p className="text-muted-foreground text-xs">Ghost</p>
                                        <div className="flex flex-wrap gap-2">
                                            <Button variant="ghost" size="sm">
                                                Small
                                            </Button>
                                            <Button variant="ghost" size="default">
                                                Default
                                            </Button>
                                            <Button variant="ghost" size="lg">
                                                Large
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </Card>
                        </div>
                    </div>
                </section>

                {/* Badges Comparison */}
                <section className="space-y-6">
                    <h2 className="font-monologue-serif text-foreground text-2xl">Badges</h2>

                    <div className="grid gap-6 lg:grid-cols-2">
                        {/* Monologue Badges */}
                        <div className="space-y-4">
                            <h3 className="font-monologue-mono text-foreground text-sm font-medium">Monologue Design System</h3>
                            <MonologueCard>
                                <MonologueCard.Body>
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Status Variants</p>
                                            <div className="flex flex-wrap gap-2">
                                                <MonologueBadge variant="active">Active</MonologueBadge>
                                                <MonologueBadge variant="inactive">Inactive</MonologueBadge>
                                                <MonologueBadge variant="error">Error</MonologueBadge>
                                                <MonologueBadge variant="pending">Pending</MonologueBadge>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Brand Variants</p>
                                            <div className="flex flex-wrap gap-2">
                                                <MonologueBadge variant="primary">Primary</MonologueBadge>
                                                <MonologueBadge variant="accent">Accent</MonologueBadge>
                                                <MonologueBadge variant="default">Default</MonologueBadge>
                                                <MonologueBadge variant="muted">Muted</MonologueBadge>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <p className="font-monologue-mono text-monologue-text-muted text-xs">Sizes</p>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <MonologueBadge variant="primary" size="sm">
                                                    Small
                                                </MonologueBadge>
                                                <MonologueBadge variant="primary" size="md">
                                                    Medium
                                                </MonologueBadge>
                                                <MonologueBadge variant="primary" size="lg">
                                                    Large
                                                </MonologueBadge>
                                            </div>
                                        </div>
                                    </div>
                                </MonologueCard.Body>
                            </MonologueCard>
                        </div>

                        {/* Existing Badges */}
                        <div className="space-y-4">
                            <h3 className="text-foreground font-mono text-sm font-medium">Current Design System (shadcn/ui)</h3>
                            <Card className="p-6">
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <p className="text-muted-foreground text-xs">Variants</p>
                                        <div className="flex flex-wrap gap-2">
                                            <Badge>Default</Badge>
                                            <Badge variant="secondary">Secondary</Badge>
                                            <Badge variant="destructive">Destructive</Badge>
                                            <Badge variant="outline">Outline</Badge>
                                        </div>
                                    </div>
                                </div>
                            </Card>
                        </div>
                    </div>
                </section>

                {/* Cards Comparison */}
                <section className="space-y-6">
                    <h2 className="font-monologue-serif text-foreground text-2xl">Cards</h2>

                    <div className="grid gap-6 lg:grid-cols-2">
                        {/* Monologue Cards */}
                        <div className="space-y-4">
                            <h3 className="font-monologue-mono text-foreground text-sm font-medium">Monologue Design System</h3>
                            <div className="space-y-4">
                                <MonologueCard variant="default">
                                    <MonologueCard.Header>Default Card</MonologueCard.Header>
                                    <MonologueCard.Body>
                                        This is the default card variant with dark background (#141414) and muted borders. Perfect for secondary
                                        content.
                                    </MonologueCard.Body>
                                    <MonologueCard.Footer>
                                        <MonologueButton variant="primary" size="sm">
                                            Action
                                        </MonologueButton>
                                        <MonologueButton variant="ghost" size="sm">
                                            Cancel
                                        </MonologueButton>
                                    </MonologueCard.Footer>
                                </MonologueCard>

                                <MonologueCard variant="elevated">
                                    <MonologueCard.Header>Elevated Card</MonologueCard.Header>
                                    <MonologueCard.Body>
                                        This is the elevated card variant with a lighter background (#282828) and visible borders. Used for emphasized
                                        content.
                                    </MonologueCard.Body>
                                </MonologueCard>
                            </div>
                        </div>

                        {/* Existing Cards */}
                        <div className="space-y-4">
                            <h3 className="text-foreground font-mono text-sm font-medium">Current Design System (shadcn/ui)</h3>
                            <div className="space-y-4">
                                <Card className="p-6">
                                    <h3 className="mb-2 text-lg font-medium">Default Card</h3>
                                    <p className="text-muted-foreground text-sm">
                                        This is the current card design used throughout the MCP Manager application. Uses shadcn/ui styling.
                                    </p>
                                    <div className="mt-4 flex gap-2">
                                        <Button size="sm">Action</Button>
                                        <Button variant="ghost" size="sm">
                                            Cancel
                                        </Button>
                                    </div>
                                </Card>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Integration Example */}
                <section className="space-y-6">
                    <h2 className="font-monologue-serif text-foreground text-2xl">MCP Integration Panel Example</h2>
                    <MonologueCard variant="elevated" padding="lg">
                        <MonologueCard.Header>Notion Integration</MonologueCard.Header>
                        <MonologueCard.Body>
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <div className="space-y-1">
                                        <p className="font-monologue-mono text-monologue-text-primary text-sm">Status</p>
                                        <MonologueBadge variant="active">Active</MonologueBadge>
                                    </div>
                                    <div className="space-y-1">
                                        <p className="font-monologue-mono text-monologue-text-primary text-sm">Last Sync</p>
                                        <p className="font-monologue-mono text-monologue-text-muted text-xs">2 hours ago</p>
                                    </div>
                                </div>
                                <div className="space-y-1">
                                    <p className="font-monologue-mono text-monologue-text-primary text-sm">Connected Workspace</p>
                                    <p className="font-monologue-mono text-monologue-text-secondary text-xs">My Personal Workspace</p>
                                </div>
                            </div>
                        </MonologueCard.Body>
                        <MonologueCard.Footer>
                            <MonologueButton variant="primary" size="sm">
                                Sync Now
                            </MonologueButton>
                            <MonologueButton variant="secondary" size="sm">
                                Settings
                            </MonologueButton>
                            <MonologueButton variant="ghost" size="sm">
                                Disconnect
                            </MonologueButton>
                        </MonologueCard.Footer>
                    </MonologueCard>
                </section>

                {/* Footer */}
                <div className="border-border border-t pt-6">
                    <p className="font-monologue-mono text-muted-foreground text-xs">
                        Design System Integration • Monologue x MCP Manager • Phase A Complete
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
