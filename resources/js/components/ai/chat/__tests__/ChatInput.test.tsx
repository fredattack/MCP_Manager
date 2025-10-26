import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ChatInput } from '../ChatInput';

describe('ChatInput', () => {
    const mockOnSend = jest.fn();
    const mockOnCancel = jest.fn();

    const defaultProps = {
        onSend: mockOnSend,
        onCancel: mockOnCancel,
        isLoading: false,
        maxLength: 1000,
    };

    beforeEach(() => {
        jest.clearAllMocks();
    });

    test('renders correctly', () => {
        render(<ChatInput {...defaultProps} />);

        expect(screen.getByPlaceholderText(/type your message/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /send/i })).toBeInTheDocument();
        expect(screen.getByText('0 / 1000')).toBeInTheDocument();
    });

    test('handles text input', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);
        await user.type(textarea, 'Hello AI!');

        expect(textarea).toHaveValue('Hello AI!');
        expect(screen.getByText('9 / 1000')).toBeInTheDocument();
    });

    test('sends message on button click', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);
        const sendButton = screen.getByRole('button', { name: /send/i });

        await user.type(textarea, 'Test message');
        await user.click(sendButton);

        expect(mockOnSend).toHaveBeenCalledWith('Test message');
        expect(textarea).toHaveValue('');
    });

    test('sends message on Enter key', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);

        await user.type(textarea, 'Test message');
        await user.keyboard('{Enter}');

        expect(mockOnSend).toHaveBeenCalledWith('Test message');
        expect(textarea).toHaveValue('');
    });

    test('allows new line with Shift+Enter', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);

        await user.type(textarea, 'Line 1');
        await user.keyboard('{Shift>}{Enter}{/Shift}');
        await user.type(textarea, 'Line 2');

        expect(textarea).toHaveValue('Line 1\nLine 2');
        expect(mockOnSend).not.toHaveBeenCalled();
    });

    test('disables input and button when loading', () => {
        render(<ChatInput {...defaultProps} isLoading={true} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);
        const sendButton = screen.getByRole('button', { name: /send/i });

        expect(textarea).toBeDisabled();
        expect(sendButton).toBeDisabled();
    });

    test('shows cancel button when loading', () => {
        render(<ChatInput {...defaultProps} isLoading={true} />);

        const cancelButton = screen.getByRole('button', { name: /cancel/i });
        expect(cancelButton).toBeInTheDocument();
    });

    test('calls onCancel when cancel button clicked', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} isLoading={true} />);

        const cancelButton = screen.getByRole('button', { name: /cancel/i });
        await user.click(cancelButton);

        expect(mockOnCancel).toHaveBeenCalled();
    });

    test('respects maxLength', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} maxLength={10} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);

        await user.type(textarea, 'This is a very long message');

        expect(textarea.value.length).toBeLessThanOrEqual(10);
    });

    test('shows character count warning when near limit', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} maxLength={100} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);
        const longText = 'a'.repeat(91);

        await user.type(textarea, longText);

        const charCount = screen.getByText('91 / 100');
        expect(charCount).toHaveClass('text-amber-600');
    });

    test('shows character count error when at limit', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} maxLength={100} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);
        const longText = 'a'.repeat(100);

        await user.type(textarea, longText);

        const charCount = screen.getByText('100 / 100');
        expect(charCount).toHaveClass('text-red-600');
    });

    test('auto-adjusts textarea height', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const textarea = screen.getByPlaceholderText(/type your message/i) as HTMLTextAreaElement;
        const initialHeight = textarea.style.height;

        // Type multiple lines
        await user.type(textarea, 'Line 1\nLine 2\nLine 3\nLine 4');

        // Height should have increased
        expect(textarea.style.height).not.toBe(initialHeight);
    });

    test('does not send empty messages', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const sendButton = screen.getByRole('button', { name: /send/i });

        await user.click(sendButton);

        expect(mockOnSend).not.toHaveBeenCalled();
    });

    test('trims whitespace before sending', async () => {
        const user = userEvent.setup();
        render(<ChatInput {...defaultProps} />);

        const textarea = screen.getByPlaceholderText(/type your message/i);
        const sendButton = screen.getByRole('button', { name: /send/i });

        await user.type(textarea, '  Test message  ');
        await user.click(sendButton);

        expect(mockOnSend).toHaveBeenCalledWith('Test message');
    });
});
