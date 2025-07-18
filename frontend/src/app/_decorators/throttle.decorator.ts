export function Throttle(limit: number) {
    let inThrottle: boolean;

    return function (target: unknown, propertyKey: string, descriptor: PropertyDescriptor) {
        const originalMethod = descriptor.value as (...args: unknown[]) => unknown;

        descriptor.value = function (...args: unknown[]) {
            if (!inThrottle) {
                originalMethod.apply(this, args);
                inThrottle = true;
                setTimeout(() => (inThrottle = false), limit);
            }
        };

        return descriptor;
    };
}
