interface IntegrationIconProps {
    service: string;
    className?: string;
    size?: number;
}

/**
 * Service slug mapping for Simple Icons CDN
 * CDN URL: https://cdn.simpleicons.org/[slug]/[hex-color]
 */
const SERVICE_SLUGS: Record<string, string> = {
    notion: 'notion',
    todoist: 'todoist',
    jira: 'jira',
    sentry: 'sentry',
    confluence: 'confluence',
    openai: 'openai',
    mistral: 'mistralai',
    gmail: 'gmail',
    calendar: 'googlecalendar',
    github: 'github',
    gitlab: 'gitlab',
};

/**
 * Brand colors for each service (hex without #)
 */
const SERVICE_COLORS: Record<string, string> = {
    notion: '000000',
    todoist: 'E44332',
    jira: '0052CC',
    sentry: '362D59',
    confluence: '172B4D',
    openai: '412991',
    mistral: 'FF7000',
    gmail: 'EA4335',
    calendar: '4285F4',
    github: '181717',
    gitlab: 'FC6D26',
};

/**
 * IntegrationIcon component
 * Displays brand logos using Simple Icons CDN
 */
export function IntegrationIcon({ service, className = '', size = 24 }: IntegrationIconProps) {
    const slug = SERVICE_SLUGS[service.toLowerCase()];
    const color = SERVICE_COLORS[service.toLowerCase()];

    if (!slug) {
        // Fallback: display first letter if service not found
        return (
            <div
                className={`flex items-center justify-center rounded-lg bg-gray-200 font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300 ${className}`}
                style={{ width: size, height: size, fontSize: size * 0.5 }}
            >
                {service.charAt(0).toUpperCase()}
            </div>
        );
    }

    // Use Simple Icons CDN with dark mode support
    const iconUrl = `https://cdn.simpleicons.org/${slug}/${color}`;

    return (
        <img
            src={iconUrl}
            alt={`${service} logo`}
            className={`object-contain ${className}`}
            style={{ width: size, height: size }}
            loading="lazy"
            onError={(e) => {
                // Fallback if CDN fails: display first letter
                const target = e.target as HTMLImageElement;
                target.style.display = 'none';
                if (target.nextElementSibling) {
                    (target.nextElementSibling as HTMLElement).style.display = 'flex';
                }
            }}
        />
    );
}

/**
 * IntegrationIconWithBackground component
 * Displays the icon with a colored background circle
 */
interface IntegrationIconWithBackgroundProps extends IntegrationIconProps {
    backgroundClassName?: string;
}

export function IntegrationIconWithBackground({ service, className = '', size = 24, backgroundClassName = '' }: IntegrationIconWithBackgroundProps) {
    const containerSize = size * 1.8;
    const iconSize = size;

    return (
        <div className={`flex items-center justify-center rounded-lg ${backgroundClassName}`} style={{ width: containerSize, height: containerSize }}>
            <IntegrationIcon service={service} size={iconSize} className={className} />
        </div>
    );
}
