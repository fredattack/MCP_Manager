import { MonologueButton } from '@/components/ui/MonologueButton';
import { useForm } from '@inertiajs/react';
import { AlertCircle, ChevronDown, ChevronRight, Github } from 'lucide-react';
import { useState } from 'react';

interface Repository {
    id: number;
    name: string;
    full_name: string;
    language?: string;
    updated_at: string;
    file_count?: number;
}

interface Props {
    isOpen: boolean;
    onClose: () => void;
    repositories?: Repository[];
}

type LLMProvider = 'openai' | 'claude' | 'mistral';

interface FormData {
    repository_id: number | null;
    task_description: string;
    llm_provider: LLMProvider;
    include_tests: boolean;
    analyze_dependencies: boolean;
}

const TASK_EXAMPLES = [
    'Add user authentication with email and password',
    'Create REST API for product management',
    'Implement file upload with validation',
    'Add real-time notifications using WebSockets',
    'Create admin dashboard with charts',
];

export function CreateWorkflowModal({ isOpen, onClose, repositories = [] }: Props) {
    const [showAdvanced, setShowAdvanced] = useState(false);
    const [step, setStep] = useState<'repository' | 'description' | 'options'>('repository');

    const { data, setData, post, processing, errors, reset } = useForm<FormData>({
        repository_id: null,
        task_description: '',
        llm_provider: 'openai',
        include_tests: true,
        analyze_dependencies: true,
    });

    const handleClose = () => {
        reset();
        setStep('repository');
        setShowAdvanced(false);
        onClose();
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        post('/api/workflows', {
            preserveScroll: true,
            onSuccess: () => {
                handleClose();
            },
        });
    };

    const canProceedToDescription = data.repository_id !== null;
    const canSubmit = canProceedToDescription && data.task_description.trim().length >= 10;

    const selectedRepository = repositories.find((r) => r.id === data.repository_id);

    if (!isOpen) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">
            <div
                data-testid="create-workflow-modal"
                className="bg-monologue-neutral-900 border-monologue-border-default max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg border shadow-2xl"
            >
                {/* Header */}
                <div className="bg-monologue-neutral-900 border-monologue-border-default sticky top-0 z-10 border-b px-6 py-4">
                    <h2 className="font-monologue-serif text-2xl text-gray-100">Create New Workflow</h2>
                    <p className="mt-1 text-sm text-gray-400">Describe what you want to build, and AI will do the rest</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6 p-6">
                    {/* Step 1: Repository Selection */}
                    <div>
                        <label className="mb-3 block text-sm font-medium text-gray-300">1. Select Repository</label>

                        {repositories.length === 0 ? (
                            <div className="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4">
                                <div className="flex items-start gap-3">
                                    <AlertCircle className="mt-0.5 shrink-0 text-amber-500" size={20} />
                                    <div>
                                        <p className="mb-2 font-medium text-amber-200">No repositories connected</p>
                                        <p className="mb-3 text-sm text-amber-300/80">Connect your GitHub or GitLab account to get started.</p>
                                        <a
                                            href="/git/connections"
                                            className="inline-flex items-center gap-2 text-sm text-cyan-400 transition-colors hover:text-cyan-300"
                                        >
                                            <Github size={16} />
                                            Connect Git Provider
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 gap-3">
                                {repositories.slice(0, 10).map((repo) => (
                                    <button
                                        key={repo.id}
                                        type="button"
                                        onClick={() => setData('repository_id', repo.id)}
                                        className={`rounded-lg border-2 p-4 text-left transition-all ${
                                            data.repository_id === repo.id
                                                ? 'border-cyan-500 bg-cyan-500/10'
                                                : 'border-monologue-border-default bg-monologue-neutral-800 hover:border-gray-600'
                                        }`}
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0 flex-1">
                                                <h4 className="truncate font-medium text-gray-200">{repo.full_name}</h4>
                                                <div className="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                                    {repo.language && (
                                                        <span className="flex items-center gap-1">
                                                            <span className="h-2 w-2 rounded-full bg-blue-500" />
                                                            {repo.language}
                                                        </span>
                                                    )}
                                                    {repo.file_count && <span>{repo.file_count} files</span>}
                                                    <span>Updated {new Date(repo.updated_at).toLocaleDateString()}</span>
                                                </div>
                                            </div>
                                            {data.repository_id === repo.id && (
                                                <div className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-cyan-500">
                                                    <svg className="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            fillRule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clipRule="evenodd"
                                                        />
                                                    </svg>
                                                </div>
                                            )}
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}

                        {errors.repository_id && <p className="mt-2 text-sm text-red-400">{errors.repository_id}</p>}
                    </div>

                    {/* Step 2: Task Description */}
                    <div className={!canProceedToDescription ? 'pointer-events-none opacity-50' : ''}>
                        <label className="mb-3 block text-sm font-medium text-gray-300">2. Describe Your Task</label>

                        <textarea
                            value={data.task_description}
                            onChange={(e) => setData('task_description', e.target.value)}
                            placeholder="Describe what you want to build in plain English..."
                            rows={5}
                            disabled={!canProceedToDescription}
                            className="bg-monologue-neutral-800 border-monologue-border-default w-full resize-none rounded-lg border px-4 py-3 text-gray-200 placeholder-gray-500 transition-all focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                        />

                        <div className="mt-2 flex items-center justify-between">
                            <div className="text-xs text-gray-500">
                                {data.task_description.length} characters
                                {data.task_description.length < 10 && ' (minimum 10)'}
                            </div>
                        </div>

                        {/* Suggested prompts */}
                        <div className="mt-3">
                            <p className="mb-2 text-xs text-gray-500">Suggested tasks:</p>
                            <div className="flex flex-wrap gap-2">
                                {TASK_EXAMPLES.map((example, index) => (
                                    <button
                                        key={index}
                                        type="button"
                                        onClick={() => setData('task_description', example)}
                                        disabled={!canProceedToDescription}
                                        className="bg-monologue-neutral-800 border-monologue-border-default rounded-full border px-3 py-1.5 text-xs text-gray-400 transition-all hover:border-cyan-500/50 hover:text-cyan-400"
                                    >
                                        {example}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {errors.task_description && <p className="mt-2 text-sm text-red-400">{errors.task_description}</p>}
                    </div>

                    {/* Step 3: Advanced Options (Collapsible) */}
                    <div className={!canProceedToDescription ? 'pointer-events-none opacity-50' : ''}>
                        <button
                            type="button"
                            onClick={() => setShowAdvanced(!showAdvanced)}
                            disabled={!canProceedToDescription}
                            className="flex items-center gap-2 text-sm text-gray-400 transition-colors hover:text-gray-200"
                        >
                            {showAdvanced ? <ChevronDown size={16} /> : <ChevronRight size={16} />}
                            <span>Advanced Options (Optional)</span>
                        </button>

                        {showAdvanced && (
                            <div className="bg-monologue-neutral-800 border-monologue-border-default mt-4 space-y-4 rounded-lg border p-4">
                                {/* LLM Provider */}
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-gray-300">AI Provider</label>
                                    <div className="grid grid-cols-3 gap-2">
                                        {(['openai', 'claude', 'mistral'] as LLMProvider[]).map((provider) => (
                                            <button
                                                key={provider}
                                                type="button"
                                                onClick={() => setData('llm_provider', provider)}
                                                className={`rounded-lg border px-4 py-2 transition-all ${
                                                    data.llm_provider === provider
                                                        ? 'border-cyan-500 bg-cyan-500/10 text-cyan-400'
                                                        : 'border-monologue-border-default text-gray-400 hover:border-gray-600'
                                                }`}
                                            >
                                                {provider === 'openai' && 'GPT-4'}
                                                {provider === 'claude' && 'Claude'}
                                                {provider === 'mistral' && 'Mistral'}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                {/* Checkboxes */}
                                <div className="space-y-3">
                                    <label className="group flex cursor-pointer items-start gap-3">
                                        <input
                                            type="checkbox"
                                            checked={data.include_tests}
                                            onChange={(e) => setData('include_tests', e.target.checked)}
                                            className="bg-monologue-neutral-900 mt-1 h-4 w-4 rounded border-gray-600 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0"
                                        />
                                        <div>
                                            <span className="text-sm text-gray-300 transition-colors group-hover:text-gray-100">Generate tests</span>
                                            <p className="mt-0.5 text-xs text-gray-500">AI will create unit and integration tests for the code</p>
                                        </div>
                                    </label>

                                    <label className="group flex cursor-pointer items-start gap-3">
                                        <input
                                            type="checkbox"
                                            checked={data.analyze_dependencies}
                                            onChange={(e) => setData('analyze_dependencies', e.target.checked)}
                                            className="bg-monologue-neutral-900 mt-1 h-4 w-4 rounded border-gray-600 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0"
                                        />
                                        <div>
                                            <span className="text-sm text-gray-300 transition-colors group-hover:text-gray-100">
                                                Analyze dependencies
                                            </span>
                                            <p className="mt-0.5 text-xs text-gray-500">Deep analysis of code dependencies and impact</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <div className="border-monologue-border-default flex items-center justify-between border-t pt-4">
                        <MonologueButton type="button" variant="ghost" onClick={handleClose} disabled={processing}>
                            Cancel
                        </MonologueButton>

                        <MonologueButton type="submit" variant="primary" disabled={!canSubmit || processing} loading={processing}>
                            {processing ? 'Creating...' : 'Create Workflow'}
                        </MonologueButton>
                    </div>
                </form>
            </div>
        </div>
    );
}
