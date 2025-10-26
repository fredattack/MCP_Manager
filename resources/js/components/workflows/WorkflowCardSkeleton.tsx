export function WorkflowCardSkeleton() {
    return (
        <div className="bg-monologue-neutral-800 border-monologue-border-default animate-pulse rounded-lg border p-6">
            {/* Status badge skeleton */}
            <div className="mb-4 flex items-center gap-2">
                <div className="h-6 w-20 rounded-full bg-gray-700" />
            </div>

            {/* Title skeleton */}
            <div className="mb-2 h-6 w-3/4 rounded bg-gray-700" />

            {/* Description skeleton */}
            <div className="mb-4 space-y-2">
                <div className="h-4 w-full rounded bg-gray-700" />
                <div className="h-4 w-2/3 rounded bg-gray-700" />
            </div>

            {/* Metadata skeleton */}
            <div className="border-monologue-border-default mt-4 flex items-center gap-4 border-t pt-4">
                <div className="h-4 w-24 rounded bg-gray-700" />
                <div className="h-4 w-20 rounded bg-gray-700" />
            </div>
        </div>
    );
}

export function WorkflowCardSkeletonGrid({ count = 3 }: { count?: number }) {
    return (
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: count }).map((_, index) => (
                <WorkflowCardSkeleton key={index} />
            ))}
        </div>
    );
}
