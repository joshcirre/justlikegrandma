import { Form, Head, Link, useForm } from '@inertiajs/react';
import { ChefHat, ExternalLink, Heart, History } from 'lucide-react';
import RecipeController from '@/actions/App/Http/Controllers/RecipeController';
import VoteController from '@/actions/App/Http/Controllers/VoteController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { index as archiveIndex } from '@/routes/archive';
import type { Recipe } from '@/types';

type PageProps = {
    recipes: Recipe[];
    myRecipe: Recipe | null;
    hasSubmittedToday: boolean;
    votesRemaining: number;
};

export default function Dashboard({
    recipes,
    myRecipe,
    hasSubmittedToday,
    votesRemaining,
}: PageProps) {
    const voteForm = useForm<{ recipe_id: number }>({ recipe_id: 0 });

    const castVote = (recipe: Recipe) => {
        voteForm.setData('recipe_id', recipe.id);
        voteForm.post(VoteController.store.url(), { preserveScroll: true });
    };

    return (
        <>
            <Head title="Today's recipes" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Today's recipes"
                        description="Share your favorite recipe, then spread your three votes across the dishes you love."
                    />
                    <div className="flex items-center gap-2">
                        <Badge variant="secondary" className="gap-1.5 py-1.5">
                            <Heart className="size-3.5" />
                            {votesRemaining}{' '}
                            {votesRemaining === 1 ? 'vote' : 'votes'} left
                        </Badge>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={archiveIndex()} prefetch>
                                <History className="size-4" />
                                Past days
                            </Link>
                        </Button>
                    </div>
                </div>

                {!hasSubmittedToday && <SubmitRecipeCard />}

                {myRecipe && (
                    <div className="rounded-xl border border-sidebar-border/70 bg-muted/40 p-4 dark:border-sidebar-border">
                        <p className="flex items-center gap-2 text-sm">
                            <ChefHat className="size-4 text-primary" />
                            You shared{' '}
                            <span className="font-medium">
                                {myRecipe.title}
                            </span>{' '}
                            today.
                        </p>
                    </div>
                )}

                {voteForm.errors.recipe_id && (
                    <p className="text-sm font-medium text-destructive">
                        {voteForm.errors.recipe_id}
                    </p>
                )}

                {recipes.length === 0 ? (
                    <div className="flex min-h-48 flex-1 items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 dark:border-sidebar-border">
                        <p className="text-sm text-muted-foreground">
                            No recipes have been shared yet today. Be the first!
                        </p>
                    </div>
                ) : (
                    <ul className="grid gap-4 lg:grid-cols-2">
                        {recipes.map((recipe) => (
                            <li
                                key={recipe.id}
                                className="flex flex-col justify-between gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                            >
                                <div className="space-y-2">
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <h3 className="font-medium">
                                                {recipe.title}
                                                {recipe.isMine && (
                                                    <span className="ml-2 text-xs font-normal text-muted-foreground">
                                                        (yours)
                                                    </span>
                                                )}
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
                                        <p className="line-clamp-4 text-sm whitespace-pre-wrap text-muted-foreground">
                                            {recipe.content}
                                        </p>
                                    )}
                                </div>

                                <Button
                                    variant={
                                        recipe.votedByMe
                                            ? 'secondary'
                                            : 'default'
                                    }
                                    size="sm"
                                    className="w-fit"
                                    disabled={
                                        !hasSubmittedToday ||
                                        votesRemaining === 0 ||
                                        recipe.votedByMe ||
                                        voteForm.processing
                                    }
                                    onClick={() => castVote(recipe)}
                                >
                                    <Heart className="size-4" />
                                    {recipe.votedByMe ? 'Voted' : 'Vote'}
                                </Button>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

function SubmitRecipeCard() {
    return (
        <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
            <Heading
                variant="small"
                title="Share today's recipe"
                description="Everyone who shares a recipe gets three votes to hand out today."
            />

            <Form
                {...RecipeController.store.form()}
                options={{ preserveScroll: true }}
                className="mt-4 space-y-4"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="title">Recipe name</Label>
                            <Input
                                id="title"
                                name="title"
                                required
                                maxLength={255}
                                placeholder="Grandma's apple pie"
                            />
                            <InputError message={errors.title} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="url">Link to the recipe</Label>
                            <Input
                                id="url"
                                name="url"
                                type="url"
                                placeholder="https://..."
                            />
                            <InputError message={errors.url} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="content">
                                Or write it out yourself
                            </Label>
                            <Textarea
                                id="content"
                                name="content"
                                placeholder="The ingredients, the steps, the secret ingredient..."
                            />
                            <InputError message={errors.content} />
                        </div>

                        <p className="text-xs text-muted-foreground">
                            Share a link, the recipe itself, or both.
                        </p>

                        {errors.recipe && (
                            <InputError message={errors.recipe} />
                        )}

                        <Button type="submit" disabled={processing}>
                            Share today's recipe
                        </Button>
                    </>
                )}
            </Form>
        </div>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
