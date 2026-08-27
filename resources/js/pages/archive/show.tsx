import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ExternalLink, Trophy } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { formatDay } from '@/lib/date';
import { index as archiveIndex } from '@/routes/archive';
import type { Recipe } from '@/types';

export default function ArchiveShow({
    date,
    recipes,
}: {
    date: string;
    recipes: Recipe[];
}) {
    const topVotes = recipes[0]?.votesCount ?? 0;

    return (
        <>
            <Head title={formatDay(date)} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div>
                    <Link
                        href={archiveIndex()}
                        className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
                    >
                        <ChevronLeft className="size-4" />
                        All past days
                    </Link>
                    <Heading
                        title={formatDay(date)}
                        description={`${recipes.length} ${recipes.length === 1 ? 'recipe' : 'recipes'} shared`}
                    />
                </div>

                <ul className="grid gap-4 lg:grid-cols-2">
                    {recipes.map((recipe, index) => (
                        <li
                            key={recipe.id}
                            className="flex flex-col justify-between gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                        >
                            <div className="space-y-2">
                                <div className="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 className="flex items-center gap-2 font-medium">
                                            {index === 0 && topVotes > 0 && (
                                                <Trophy className="size-4 text-amber-500" />
                                            )}
                                            {recipe.title}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            by {recipe.author}
                                        </p>
                                    </div>
                                    <Badge variant="secondary">
                                        {recipe.votesCount}{' '}
                                        {recipe.votesCount === 1
                                            ? 'vote'
                                            : 'votes'}
                                    </Badge>
                                </div>

                                {recipe.url && (
                                    <a
                                        href={recipe.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-1 text-sm text-primary underline-offset-4 hover:underline"
                                    >
                                        View recipe
                                        <ExternalLink className="size-3.5" />
                                    </a>
                                )}

                                {recipe.content && (
                                    <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                        {recipe.content}
                                    </p>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
            </div>
        </>
    );
}

ArchiveShow.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Past days',
            href: '/archive',
        },
    ],
};
