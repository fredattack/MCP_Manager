import { Message } from '@/types/ai/claude.types';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MessageItem } from '../MessageItem';

// Mock clipboard API
Object.assign(navigator, {
    clipboard: {
        writeText: jest.fn(),
    },
});

describe('MessageItem', () => {
    const mockOnRegenerate = jest.fn();
    const mockOnEdit = jest.fn();

    const userMessage: Message = {
        id: '1',
        role: 'user',
        content: 'Hello AI!',
        timestamp: new Date(),
        status: 'sent',
    };

    const assistantMessage: Message = {
        id: '2',
        role: 'assistant',
        content: 'Hello! How can I help you today?',
        timestamp: new Date(),
        status: 'sent',
        metadata: {
            model: 'gpt-4',
        },
    };

    const defaultProps = {
        message: userMessage,
        onRegenerate: mockOnRegenerate,
        onEdit: mockOnEdit,
        isStreaming: false,
    };

    beforeEach(() => {
        jest.clearAllMocks();
    });

    test('renders user message correctly', () => {
        render(<MessageItem {...defaultProps} />);

        expect(screen.getByText('Hello AI!')).toBeInTheDocument();
        expect(screen.getByText('You')).toBeInTheDocument();
    });

    test('renders assistant message correctly', () => {
        render(<MessageItem {...defaultProps} message={assistantMessage} />);

        expect(screen.getByText('Hello! How can I help you today?')).toBeInTheDocument();
        expect(screen.getByText('Assistant')).toBeInTheDocument();
    });

    test('shows timestamp on hover', async () => {
        const user = userEvent.setup();
        render(<MessageItem {...defaultProps} />);

        const messageContainer = screen.getByText('Hello AI!').closest('div');

        await user.hover(messageContainer!);

        await waitFor(() => {
            const time = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
            expect(screen.getByText(time)).toBeInTheDocument();
        });
    });

    test('shows actions menu on hover for assistant messages', async () => {
        const user = userEvent.setup();
        render(<MessageItem {...defaultProps} message={assistantMessage} />);

        const messageContainer = screen.getByText('Hello! How can I help you today?').closest('div');

        await user.hover(messageContainer!);

        await waitFor(() => {
            expect(screen.getByLabelText('Copy')).toBeInTheDocument();
            expect(screen.getByLabelText('Regenerate')).toBeInTheDocument();
        });
    });

    test('copies message content to clipboard', async () => {
        const user = userEvent.setup();
        render(<MessageItem {...defaultProps} message={assistantMessage} />);

        const messageContainer = screen.getByText('Hello! How can I help you today?').closest('div');
        await user.hover(messageContainer!);

        const copyButton = await screen.findByLabelText('Copy');
        await user.click(copyButton);

        expect(navigator.clipboard.writeText).toHaveBeenCalledWith('Hello! How can I help you today?');
    });

    test('calls onRegenerate when regenerate button clicked', async () => {
        const user = userEvent.setup();
        render(<MessageItem {...defaultProps} message={assistantMessage} />);

        const messageContainer = screen.getByText('Hello! How can I help you today?').closest('div');
        await user.hover(messageContainer!);

        const regenerateButton = await screen.findByLabelText('Regenerate');
        await user.click(regenerateButton);

        expect(mockOnRegenerate).toHaveBeenCalledWith('2');
    });

    test('shows loading state when streaming', () => {
        render(<MessageItem {...defaultProps} message={{ ...assistantMessage, status: 'sending' }} isStreaming={true} />);

        expect(screen.getByTestId('typing-indicator')).toBeInTheDocument();
    });

    test('shows error state', () => {
        const errorMessage = {
            ...assistantMessage,
            status: 'error' as const,
            content: 'Sorry, an error occurred.',
        };

        render(<MessageItem {...defaultProps} message={errorMessage} />);

        expect(screen.getByText('Sorry, an error occurred.')).toBeInTheDocument();
        const errorIcon = screen.getByText('⚠️');
        expect(errorIcon).toBeInTheDocument();
    });

    test('renders markdown content', () => {
        const markdownMessage = {
            ...assistantMessage,
            content: '**Bold text** and *italic text*',
        };

        render(<MessageItem {...defaultProps} message={markdownMessage} />);

        expect(screen.getByText('Bold text')).toHaveStyle('font-weight: 700');
        expect(screen.getByText('italic text')).toHaveStyle('font-style: italic');
    });

    test('renders code blocks with syntax highlighting', () => {
        const codeMessage = {
            ...assistantMessage,
            content: '```javascript\nconst hello = "world";\n```',
        };

        render(<MessageItem {...defaultProps} message={codeMessage} />);

        expect(screen.getByText('const hello = "world";')).toBeInTheDocument();
        expect(screen.getByText('javascript')).toBeInTheDocument();
    });

    test('shows model information for assistant messages', () => {
        render(<MessageItem {...defaultProps} message={assistantMessage} />);

        expect(screen.getByText('GPT-4')).toBeInTheDocument();
    });

    test('handles edit action for user messages', async () => {
        const user = userEvent.setup();
        render(<MessageItem {...defaultProps} />);

        const messageContainer = screen.getByText('Hello AI!').closest('div');
        await user.hover(messageContainer!);

        const editButton = await screen.findByLabelText('Edit');
        await user.click(editButton);

        expect(mockOnEdit).toHaveBeenCalledWith('1', 'Hello AI!');
    });

    test('shows sending status', () => {
        const sendingMessage = {
            ...userMessage,
            status: 'sending' as const,
        };

        render(<MessageItem {...defaultProps} message={sendingMessage} />);

        expect(screen.getByText('Sending...')).toBeInTheDocument();
    });

    test('truncates very long messages', () => {
        const longMessage = {
            ...assistantMessage,
            content: 'a'.repeat(5000),
        };

        render(<MessageItem {...defaultProps} message={longMessage} />);

        const content = screen.getByText(/a+/);
        expect(content.textContent?.length).toBeLessThan(5000);
    });
});
