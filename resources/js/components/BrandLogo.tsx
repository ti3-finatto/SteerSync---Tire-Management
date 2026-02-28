import { cn } from '@/lib/utils';

type BrandLogoProps = {
    className?: string;
    imageClassName?: string;
    alt?: string;
    compact?: boolean;
};

export default function BrandLogo({
    className,
    imageClassName,
    alt = 'SteerSync',
    compact = false,
}: BrandLogoProps) {
    return (
        <span className={cn('inline-flex items-center', className)}>
            <img
                src="/brand/logo-light.png"
                alt={alt}
                className={cn(
                    'dark:hidden',
                    compact ? 'h-9 w-9 object-contain' : 'h-24 w-auto',
                    imageClassName,
                )}
            />
            <img
                src="/brand/logo-dark.png"
                alt={alt}
                className={cn(
                    'hidden dark:block',
                    compact ? 'h-9 w-9 object-contain' : 'h-24 w-auto',
                    imageClassName,
                )}
            />
        </span>
    );
}
