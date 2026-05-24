export default function AppLogo() {
    return (
        <>
            <div className="flex items-center gap-1.5">
                <div className="flex size-9 items-center justify-center rounded-lg border border-sidebar-border/70 bg-white p-1 shadow-sm">
                    <img
                        src="/ipn.png"
                        alt="IPN"
                        className="size-7 object-contain"
                    />
                </div>
                <div className="flex size-9 items-center justify-center rounded-lg border border-sidebar-border/70 bg-white p-1 shadow-sm">
                    <img
                        src="/logocicsstf.png"
                        alt="CICS UST - IPN"
                        className="size-7 object-contain"
                    />
                </div>
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    CICS UST - IPN
                </span>
            </div>
        </>
    );
}
