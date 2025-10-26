export function WorkflowDetailSkeleton() {
    return (
        <div className="mx-auto max-w-5xl animate-pulse px-4 py-8 sm:px-6 lg:px-8">
            {/* Breadcrumb skeleton */}
            <div className="mb-6 h-5 w-32 rounded bg-gray-700" />

            {/* Header section */}
            <div className="mb-8">
                <div className="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex-1">
                        {/* Status badge */}
                        <div className="mb-3 h-7 w-24 rounded-full bg-gray-700" />

                        {/* Title */}
                        <div className="mb-3 h-10 w-3/4 rounded bg-gray-700" />

                        {/* Subtitle */}
                        <div className="h-5 w-1/2 rounded bg-gray-700" />
                    </div>

                    {/* Action buttons */}
                    <div className="flex items-center gap-2">
                        <div className="h-10 w-24 rounded bg-gray-700" />
                        <div className="h-10 w-10 rounded bg-gray-700" />
                        <div className="h-10 w-10 rounded bg-gray-700" />
                    </div>
                </div>

                {/* Metadata */}
                <div className="flex gap-4">
                    <div className="h-4 w-32 rounded bg-gray-700" />
                    <div className="h-4 w-28 rounded bg-gray-700" />
                </div>
            </div>

            {/* Main content card */}
            <div className="bg-monologue-neutral-800 border-monologue-border-default rounded-lg border p-6">
                {/* Card header */}
                <div className="mb-4 h-6 w-48 rounded bg-gray-700" />

                {/* Card body - Timeline skeleton */}
                <div className="space-y-4">
                    {[1, 2, 3, 4].map((i) => (
                        <div key={i} className="flex items-start gap-4">
                            <div className="h-10 w-10 shrink-0 rounded-full bg-gray-700" />
                            <div className="flex-1 space-y-2">
                                <div className="h-5 w-48 rounded bg-gray-700" />
                                <div className="h-4 w-64 rounded bg-gray-700" />
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Secondary content card */}
            <div className="bg-monologue-neutral-800 border-monologue-border-default mt-6 rounded-lg border p-6">
                <div className="mb-4 h-6 w-32 rounded bg-gray-700" />
                <div className="space-y-3">
                    <div className="h-4 w-full rounded bg-gray-700" />
                    <div className="h-4 w-5/6 rounded bg-gray-700" />
                    <div className="h-4 w-4/6 rounded bg-gray-700" />
                </div>
            </div>
        </div>
    );
}
