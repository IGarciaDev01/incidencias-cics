import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="8" fill="#1e40af"/>
            <path
                d="M8 32V14L13 10V32H8Z"
                fill="#60a5fa"
            />
            <path
                d="M14.5 32V12L19.5 8.5V32H14.5Z"
                fill="#93c5fd"
            />
            <path
                d="M21 32V12L26 8.5V32H21Z"
                fill="#60a5fa"
            />
            <path
                d="M27.5 32V14L32.5 10V32H27.5Z"
                fill="#93c5fd"
            />
            <rect x="10" y="32" width="21" height="3" fill="#2563eb"/>
            <rect x="7" y="35" width="27" height="2" rx="1" fill="#1e3a8a"/>
        </svg>
    );
}