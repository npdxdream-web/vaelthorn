import { Link } from "react-router";
import { ArrowLeft, Clock, MessageSquare, MapPin, ScrollText } from "lucide-react";
import { threads, characters } from "../data/mockData";

const TAG_COLORS: Record<string, string> = {
  Quest: "#c8a84b",
  Artifact: "#9b8fc8",
  Social: "#6890c8",
  Trade: "#5a8c5a",
  Crafting: "#c87c3a",
  Weapon: "#c84848",
  Magic: "#9b8fc8",
  Ritual: "#c84848",
  Mystery: "#746a5a",
  Exploration: "#5a8c6a",
};

export function RecentActivityPage() {
  const character = characters.aelric;

  const authoredThreads = threads.filter((t) => t.author.id === character.id);
  const joinedThreads = threads.filter((t) => t.author.id !== character.id);

  return (
    <div className="vaelthorn-page mx-auto max-w-3xl px-6 py-12 pb-32">
      <Link
        to="/"
        className="mb-6 inline-flex items-center gap-2 text-sm text-[#a8a6a3] hover:text-[#c8a84b] transition-colors"
      >
        <ArrowLeft className="h-4 w-4" />
        Back to Home
      </Link>

      {/* Page Header */}
      <div className="mb-8">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center border border-[#c8a84b]/30 bg-[#141210]">
            <Clock className="h-5 w-5 text-[#c8a84b]" />
          </div>
          <div>
            <h1 className="font-display text-2xl tracking-wide text-[#c8a84b]">
              Recent Activity
            </h1>
            <p className="text-xs tracking-widest text-[#746a5a] uppercase">
              Chronicles &amp; Threads Joined
            </p>
          </div>
        </div>
      </div>

      {/* My Chronicles */}
      <section className="mb-10">
        <div className="mb-4 flex items-center gap-3">
          <span className="text-[10px] text-[#c8a84b]/40">♦</span>
          <h2 className="font-display text-sm tracking-[0.2em] text-[#c8a84b]/70 uppercase">
            My Chronicles
          </h2>
          <div className="flex-1 border-t border-[#c8a84b]/10" />
          <span className="text-xs text-[#746a5a]">{authoredThreads.length} threads</span>
        </div>

        {authoredThreads.length > 0 ? (
          <div className="space-y-3">
            {authoredThreads.map((thread) => (
              <ThreadCard key={thread.id} thread={thread} isAuthor />
            ))}
          </div>
        ) : (
          <EmptyState message="No chronicles written yet. Begin your story." />
        )}
      </section>

      {/* Threads Joined */}
      <section>
        <div className="mb-4 flex items-center gap-3">
          <span className="text-[10px] text-[#c8a84b]/40">♦</span>
          <h2 className="font-display text-sm tracking-[0.2em] text-[#c8a84b]/70 uppercase">
            Threads Joined
          </h2>
          <div className="flex-1 border-t border-[#c8a84b]/10" />
          <span className="text-xs text-[#746a5a]">{joinedThreads.length} threads</span>
        </div>

        {joinedThreads.length > 0 ? (
          <div className="space-y-3">
            {joinedThreads.map((thread) => (
              <ThreadCard key={thread.id} thread={thread} isAuthor={false} />
            ))}
          </div>
        ) : (
          <EmptyState message="No threads joined yet. Explore the world." />
        )}
      </section>
    </div>
  );
}

function ThreadCard({
  thread,
  isAuthor,
}: {
  thread: (typeof threads)[0];
  isAuthor: boolean;
}) {
  return (
    <Link
      to={`/thread/${thread.id}`}
      className="group block border border-[#c8a84b]/12 bg-[#0e0c09] p-4 transition-all hover:border-[#c8a84b]/35 hover:bg-[#141210]"
    >
      <div className="flex items-start justify-between gap-4">
        <div className="flex-1 min-w-0">
          <div className="mb-1.5 flex items-center gap-2">
            {isAuthor && (
              <span className="rounded-sm bg-[#c8a84b]/10 px-1.5 py-0.5 text-[9px] tracking-wider text-[#c8a84b]/70 uppercase">
                Author
              </span>
            )}
            <h3 className="truncate text-sm font-medium text-[#efe7d2] group-hover:text-[#c8a84b] transition-colors">
              {thread.title}
            </h3>
          </div>

          <div className="flex flex-wrap items-center gap-3 text-xs text-[#746a5a]">
            <span className="flex items-center gap-1">
              <MapPin className="h-3 w-3" />
              {thread.village}
            </span>
            <span className="flex items-center gap-1">
              <MessageSquare className="h-3 w-3" />
              {thread.replies} replies
            </span>
            {!isAuthor && (
              <span className="flex items-center gap-1 text-[#a8a6a3]">
                <ScrollText className="h-3 w-3" />
                {thread.author.name}
              </span>
            )}
          </div>

          {thread.tags && thread.tags.length > 0 && (
            <div className="mt-2 flex flex-wrap gap-1.5">
              {thread.tags.map((tag) => (
                <span
                  key={tag}
                  className="rounded-sm px-1.5 py-0.5 text-[9px] tracking-wider uppercase"
                  style={{
                    color: TAG_COLORS[tag] ?? "#746a5a",
                    backgroundColor: `${TAG_COLORS[tag] ?? "#746a5a"}15`,
                  }}
                >
                  {tag}
                </span>
              ))}
            </div>
          )}
        </div>

        <div className="flex shrink-0 items-center gap-1 text-[10px] text-[#746a5a]">
          <Clock className="h-3 w-3" />
          {thread.lastPost}
        </div>
      </div>
    </Link>
  );
}

function EmptyState({ message }: { message: string }) {
  return (
    <div className="flex flex-col items-center gap-3 border border-[#c8a84b]/08 bg-[#0e0c09] py-12 text-center">
      <ScrollText className="h-10 w-10 text-[#746a5a]/40" />
      <p className="text-sm italic text-[#746a5a]">{message}</p>
    </div>
  );
}
