import { cn } from '@/lib/utils';
import { Message } from '@/types/ai/claude.types';
import { FC, memo, useCallback, useEffect, useRef } from 'react';
import AutoSizer from 'react-virtualized-auto-sizer';
import { VariableSizeList as List } from 'react-window';
import { MessageItem } from './MessageItem';

interface VirtualizedMessageListProps {
    messages: Message[];
    onRegenerate: (messageId: string) => void;
    onEdit: (messageId: string, content: string) => void;
    streamingMessageId: string | null;
    className?: string;
}

interface ItemData {
    messages: Message[];
    onRegenerate: (messageId: string) => void;
    onEdit: (messageId: string, content: string) => void;
    streamingMessageId: string | null;
}

const ITEM_SIZE_CACHE = new Map<string, number>();
const ESTIMATED_ITEM_SIZE = 120;

const Row = memo<{
    index: number;
    style: React.CSSProperties;
    data: ItemData;
}>(({ index, style, data }) => {
    const { messages, onRegenerate, onEdit, streamingMessageId } = data;
    const message = messages[index];

    return (
        <div style={style}>
            <MessageItem message={message} onRegenerate={onRegenerate} onEdit={onEdit} isStreaming={streamingMessageId === message.id} />
        </div>
    );
});

Row.displayName = 'MessageRow';

export const VirtualizedMessageList: FC<VirtualizedMessageListProps> = ({ messages, onRegenerate, onEdit, streamingMessageId, className }) => {
    const listRef = useRef<List>(null);
    const rowHeights = useRef<Map<number, number>>(new Map());
    const isScrollingToBottom = useRef(false);

    // Get item size from cache or estimate
    const getItemSize = useCallback(
        (index: number): number => {
            const cachedHeight = rowHeights.current.get(index);
            if (cachedHeight !== undefined) {
                return cachedHeight;
            }

            // Try to get from global cache based on message ID
            const message = messages[index];
            if (message) {
                const cachedSize = ITEM_SIZE_CACHE.get(message.id);
                if (cachedSize !== undefined) {
                    rowHeights.current.set(index, cachedSize);
                    return cachedSize;
                }
            }

            // Estimate size based on content length
            const contentLength = message?.content.length || 0;
            const estimatedLines = Math.ceil(contentLength / 80);
            const estimatedHeight = Math.max(ESTIMATED_ITEM_SIZE, estimatedLines * 24 + 60);

            return estimatedHeight;
        },
        [messages],
    );

    // Set item size after render
    const setItemSize = useCallback(
        (index: number, size: number) => {
            const message = messages[index];
            if (!message) return;

            const currentSize = rowHeights.current.get(index);
            if (currentSize === size) return;

            rowHeights.current.set(index, size);
            ITEM_SIZE_CACHE.set(message.id, size);

            // Reset the cached positions after this item
            if (listRef.current) {
                listRef.current.resetAfterIndex(index);
            }
        },
        [messages],
    );

    // Auto-scroll to bottom when new messages arrive
    useEffect(() => {
        if (messages.length > 0 && listRef.current && !isScrollingToBottom.current) {
            isScrollingToBottom.current = true;
            listRef.current.scrollToItem(messages.length - 1, 'end');

            // Reset flag after scrolling
            setTimeout(() => {
                isScrollingToBottom.current = false;
            }, 100);
        }
    }, [messages.length]);

    // Clear cache when messages change significantly
    useEffect(() => {
        if (messages.length === 0) {
            rowHeights.current.clear();
        }
    }, [messages.length]);

    const itemData: ItemData = {
        messages,
        onRegenerate,
        onEdit,
        streamingMessageId,
    };

    if (messages.length === 0) {
        return (
            <div className={cn('flex h-full items-center justify-center', className)}>
                <div className="text-center">
                    <p className="text-gray-500 dark:text-gray-400">No messages yet</p>
                    <p className="mt-2 text-sm text-gray-400 dark:text-gray-500">Start a conversation by typing a message below</p>
                </div>
            </div>
        );
    }

    return (
        <div className={cn('h-full', className)}>
            <AutoSizer>
                {({ height, width }) => (
                    <List
                        ref={listRef}
                        height={height}
                        itemCount={messages.length}
                        itemSize={getItemSize}
                        width={width}
                        itemData={itemData}
                        overscanCount={3}
                        estimatedItemSize={ESTIMATED_ITEM_SIZE}
                        onItemsRendered={({ visibleStartIndex, visibleStopIndex }) => {
                            // Measure visible items after render
                            for (let i = visibleStartIndex; i <= visibleStopIndex; i++) {
                                const row = document.querySelector(`[data-message-index="${i}"]`);
                                if (row) {
                                    const height = row.getBoundingClientRect().height;
                                    setItemSize(i, height);
                                }
                            }
                        }}
                    >
                        {Row}
                    </List>
                )}
            </AutoSizer>
        </div>
    );
};
