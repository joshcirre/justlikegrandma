import { Head, Link } from '@inertiajs/react';
import { ChevronRight, History } from 'lucide-react';
import Heading from '@/components/heading';
import { formatDay } from '@/lib/date';
import { dashboard } from '@/routes';
import { show as showDay } from '@/routes/archive';
import type { DaySummary } from '@/types';

export default function ArchiveIndex({ days }: { days: DaySummary[] }) {
    return (
        <>
            <Head title="Past days" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Past days"
                    description="Revisit every recipe swap and see which dishes won the day."
                />

                {days.length === 0 ? (
                    <div className="flex min-h-48 flex-1 items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 dark:border-sidebar-border">
                        <p className="text-sm text-muted-foreground">
                            Nothing in the archive yet. The first day is still
                            being cooked up!
                        </p>
                    </div>
                ) : (
                    <ul className="grid gap-3">
                        {days.map((day) => (
                            <li key={day.date}>
                                <Link
                                    href={showDay({ date: day.date })}
                                    className="flex items-center justify-between gap-4 rounded-xl border border-sidebar-border/70 p-4 text-sm transition-colors hover:bg-muted/50 dark:border-sidebar-border"
                                >
                                    <span className="flex items-center gap-3">
                                        <History className="size-4 text-muted-foreground" />
                                        {formatDay(day.date)}
                                    </span>
                                    <span className="flex items-center gap-3 text-muted-foreground">
                                        {day.recipesCount}{' '}
                                        {day.recipesCount === 1
                                            ? 'recipe'
                                            : 'recipes'}
                                        {' · '}
                                        {day.votesCount}{' '}
                                        {day.votesCount === 1
                                            ? 'vote'
                                            : 'votes'}
                                        <ChevronRight className="size-4" />
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

ArchiveIndex.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Past days',
            href: '/archive',
        },
    ],
};
