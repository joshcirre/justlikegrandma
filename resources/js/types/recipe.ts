export interface Recipe {
    id: number;
    title: string;
    url: string | null;
    content: string | null;
    author: string;
    votesCount: number;
    votedByMe: boolean;
    isMine: boolean;
}

export interface DaySummary {
    date: string;
    recipesCount: number;
    votesCount: number;
}
