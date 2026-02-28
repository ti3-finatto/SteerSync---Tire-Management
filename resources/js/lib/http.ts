function getMetaCsrfToken(): string {
    if (typeof document === 'undefined') {
        return '';
    }

    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')?.trim() ?? '';
}

function getCookieValue(name: string): string {
    if (typeof document === 'undefined') {
        return '';
    }

    const cookie = document.cookie
        .split('; ')
        .find((entry) => entry.startsWith(`${name}=`));

    if (!cookie) {
        return '';
    }

    return decodeURIComponent(cookie.slice(name.length + 1));
}

export function withCsrfHeaders(headers?: HeadersInit): Headers {
    const mergedHeaders = new Headers(headers);
    const metaToken = getMetaCsrfToken();

    if (metaToken) {
        mergedHeaders.set('X-CSRF-TOKEN', metaToken);
    } else {
        const xsrfToken = getCookieValue('XSRF-TOKEN');

        if (xsrfToken) {
            mergedHeaders.set('X-XSRF-TOKEN', xsrfToken);
        }
    }

    mergedHeaders.set('X-Requested-With', 'XMLHttpRequest');

    return mergedHeaders;
}

export function fetchWithCsrf(input: RequestInfo | URL, init: RequestInit = {}): Promise<Response> {
    return fetch(input, {
        ...init,
        credentials: init.credentials ?? 'same-origin',
        headers: withCsrfHeaders(init.headers),
    });
}
