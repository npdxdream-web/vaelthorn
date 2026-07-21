import { type ReactNode, useEffect, useMemo, useState } from "react";
import { useNavigate, useParams, Link } from "react-router";
import {
  ArrowLeft,
  Bold,
  CheckCircle,
  Clock,
  Crown,
  Edit3,
  Feather,
  FlameKindling,
  Italic,
  List,
  MapPin,
  Pencil,
  Quote,
  Scroll,
  Send,
  Shield,
  Sparkles,
  Trash2,
  Underline,
  Zap,
} from "lucide-react";
import { CharacterModule } from "../components/CharacterModule";
import { Button } from "../components/ui/button";
import { Textarea } from "../components/ui/textarea";

type CharacterStats = Record<string, number | string | null | undefined> | null | undefined;

interface CharacterBadge {
  name?: string;
  icon?: string | null;
  description?: string | null;
}

interface ThreadPost {
  id: string | number;
  content: string;
  status: string;
  is_owner: boolean;
  can_edit: boolean;
  can_approve: boolean;
  created_at: string;
  character?: {
    id: string | number | null;
    name?: string | null;
    kingdom?: string | null;
    location?: string | null;
    title?: string | null;
    rank?: string | null;
    auto_rank?: string | null;
    status?: string | null;
    avatar_url?: string | null;
    stats?: CharacterStats;
    badges?: CharacterBadge[];
  } | null;
}

interface ThreadApiResponse {
  thread: {
    id: string | number;
    title: string;
    status: string;
    status_label: string;
    moderation_message: string | null;
    user_id: number | null;
    city: { id: string | number; name: string; kingdom?: string };
  };
  posts: ThreadPost[];
  viewer: {
    is_admin: boolean;
    character_id: number | null;
    user_id: number | null;
  };
}

const STATUS_COLORS: Record<string, string> = {
  approved: "text-emerald-300 bg-emerald-950/20 border-emerald-400/30",
  open: "text-emerald-300 bg-emerald-950/20 border-emerald-400/30",
  pending: "text-amber-300 bg-amber-950/20 border-amber-400/35",
  draft: "text-slate-300 bg-slate-950/25 border-slate-400/25",
  request_edit: "text-orange-300 bg-orange-950/20 border-orange-400/30",
  rejected: "text-rose-300 bg-rose-950/20 border-rose-400/30",
  locked: "text-slate-300 bg-slate-900/30 border-slate-500/30",
  archived: "text-indigo-300 bg-indigo-950/20 border-indigo-400/30",
};

const STAT_ROWS = [
  { label: "LVL", keys: ["level"], max: 100, color: "#c8a84b" },
  { label: "STR", keys: ["str", "strength"], max: 100, color: "#c8a84b" },
  { label: "AGI", keys: ["agi", "dexterity"], max: 100, color: "#7ab0d4" },
  { label: "HP", keys: ["hp"], max: 100, color: "#c05050" },
  { label: "MP", keys: ["mana", "mp"], max: 100, color: "#7060b8" },
];

function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
}

function normalizeCharacter(post: ThreadPost) {
  const character = post.character ?? {};
  const name = character.name?.trim() || "Unknown Character";
  return {
    id: character.id ?? null,
    name,
    initial: name.charAt(0).toUpperCase() || "?",
    kingdom: character.kingdom || "Unknown Kingdom",
    location: character.location || character.kingdom || "Uncharted",
    rank: character.rank || character.auto_rank || "Wandering Scribe",
    title: character.title || character.rank || character.auto_rank || "Wandering Scribe",
    status: character.status || (post.status === "approved" || post.status === "open" ? "Active" : "Awaiting Council"),
    avatarUrl: character.avatar_url || null,
    stats: character.stats,
    badges: character.badges ?? [],
  };
}

function getStatValue(stats: CharacterStats, keys: string[], fallback = 0) {
  if (!stats || Array.isArray(stats)) return fallback;

  for (const key of keys) {
    const raw = stats[key];
    const value = typeof raw === "string" ? Number(raw) : raw;
    if (typeof value === "number" && Number.isFinite(value)) return value;
  }

  return fallback;
}

function statusLabel(status: string, fallback?: string) {
  if (fallback) return fallback;
  return status.replace(/_/g, " ");
}

function ThreadStatusBadge({ status, label }: { status: string; label?: string }) {
  const cls = STATUS_COLORS[status] || STATUS_COLORS.pending;
  return (
    <span className={`inline-flex items-center gap-1.5 border px-2.5 py-1 font-display text-[0.65rem] uppercase tracking-[0.18em] ${cls}`}>
      <span className="h-1.5 w-1.5 rounded-full bg-current shadow-[0_0_7px_currentColor]" />
      {statusLabel(status, label)}
    </span>
  );
}

function ThinRule() {
  return (
    <div className="flex w-full items-center gap-2">
      <div className="h-px flex-1 bg-[#c8a84b]/15" />
      <div className="h-1 w-1 rotate-45 bg-[#c8a84b]/40" />
      <div className="h-px flex-1 bg-[#c8a84b]/15" />
    </div>
  );
}

function AttributeBar({
  label,
  value,
  max,
  color,
}: {
  label: string;
  value: number;
  max: number;
  color: string;
}) {
  const pct = Math.max(0, Math.min(100, Math.round((value / max) * 100)));

  return (
    <div className="flex items-center gap-2">
      <span className="w-7 shrink-0 font-display text-[0.65rem] tracking-wider text-[#746a5a]">{label}</span>
      <div className="h-[3px] flex-1 overflow-hidden rounded-full bg-[#211d17]">
        <div
          className="h-full rounded-full"
          style={{
            width: `${pct}%`,
            background: `linear-gradient(90deg, ${color}66, ${color})`,
            boxShadow: `0 0 5px ${color}66`,
          }}
        />
      </div>
      <span className="w-8 shrink-0 text-right font-display text-[0.65rem] text-[#c8a84b]/65">{value}</span>
    </div>
  );
}

function CharacterPortrait({ post }: { post: ThreadPost }) {
  const character = normalizeCharacter(post);

  return (
    <div className="relative">
      <div className="pointer-events-none absolute -inset-[3px] shadow-[0_0_18px_rgba(200,168,75,0.12)]" />
      <div className="relative border border-[#c8a84b]/40 bg-[#0e0c0a] shadow-[inset_0_0_0_2px_rgba(200,168,75,0.06),inset_0_0_24px_rgba(0,0,0,0.55)]">
        <div className="relative aspect-[3/4.2] overflow-hidden bg-[linear-gradient(175deg,#1c1915_0%,#0d0c0a_60%,#090807_100%)]">
          {character.avatarUrl ? (
            <img
              src={character.avatarUrl}
              alt={character.name}
              className="h-full w-full object-cover opacity-90"
            />
          ) : (
            <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 opacity-25">
              <div className="flex h-16 w-16 items-center justify-center rounded-full border border-[#c8a84b]/60 shadow-[0_0_12px_rgba(200,168,75,0.15)]">
                <Crown className="h-7 w-7 text-[#c8a84b]" />
              </div>
              <span className="font-decorative text-5xl text-[#c8a84b]/70">{character.initial}</span>
            </div>
          )}
          <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_50%_35%,transparent_42%,rgba(0,0,0,0.6)_100%)]" />
          <div className="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#090807] to-transparent" />
          <div className="absolute left-3 right-3 top-2.5 flex items-center gap-1.5 opacity-40">
            <div className="h-px flex-1 bg-[#c8a84b]" />
            <div className="h-1 w-1 rotate-45 bg-[#c8a84b]" />
            <div className="h-px flex-1 bg-[#c8a84b]" />
          </div>
          <div className="absolute right-3 top-3 flex items-center gap-1 border border-[#c8a84b]/20 bg-[#090807]/85 px-1.5 py-0.5">
            <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_5px_rgba(74,222,128,0.75)]" />
            <span className="font-display text-[0.6rem] tracking-wider text-emerald-300">{character.status}</span>
          </div>
        </div>
      </div>
      {["top-0 left-0 border-t border-l", "top-0 right-0 border-t border-r", "bottom-0 left-0 border-b border-l", "bottom-0 right-0 border-b border-r"].map((cls) => (
        <div key={cls} className={`absolute h-4 w-4 border-[#c8a84b]/70 ${cls}`} />
      ))}
    </div>
  );
}

function PostCharacterPanel({ post }: { post: ThreadPost }) {
  const character = normalizeCharacter(post);
  const characterHref = character.id ? `/character/${character.id}` : undefined;
  const hasStats = STAT_ROWS.some((row) => getStatValue(character.stats, row.keys, 0) > 0);

  const panel = (
    <aside className="flex h-full flex-col border-r border-[#c8a84b]/15 bg-[linear-gradient(180deg,#161310_0%,#0e0c0a_100%)] p-4 lg:w-[278px]">
      <CharacterPortrait post={post} />

      <div className="pt-4 text-center">
        <div className="font-display text-base font-semibold leading-tight tracking-wide text-[#d4b96e] shadow-black [text-shadow:0_0_20px_rgba(200,168,75,0.25)]">
          {character.name}
        </div>
        <div className="mt-1 font-display text-[0.65rem] uppercase tracking-[0.24em] text-[#746a5a]">
          {character.title}
        </div>
      </div>

      <div className="mt-4">
        <ThinRule />
      </div>

      <div className="mt-4 space-y-3">
        {[
          { icon: MapPin, label: "Kingdom", value: character.kingdom },
          { icon: Feather, label: "Location", value: character.location },
          { icon: Shield, label: "Rank", value: character.rank },
          { icon: Scroll, label: "Identity", value: "Public Character" },
        ].map(({ icon: Icon, label, value }) => (
          <div key={label} className="flex items-start gap-2">
            <Icon className="mt-0.5 h-3 w-3 shrink-0 text-[#c8a84b]/45" />
            <div className="min-w-0">
              <div className="font-display text-[0.6rem] uppercase leading-none tracking-[0.18em] text-[#746a5a]">{label}</div>
              <div className="font-chronicle mt-1 break-words text-sm leading-tight text-[#c4b898]/90">{value}</div>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-4">
        <ThinRule />
      </div>

      <div className="mt-4">
        <div className="archive-label mb-3">Attributes</div>
        {hasStats ? (
          <div className="space-y-2.5">
            {STAT_ROWS.map((row) => (
              <AttributeBar
                key={row.label}
                label={row.label}
                value={getStatValue(character.stats, row.keys, 0)}
                max={row.max}
                color={row.color}
              />
            ))}
          </div>
        ) : (
          <div className="rounded border border-[#c8a84b]/12 bg-[#0f0d0b] p-3 text-sm text-[#746a5a]">
            No public stats recorded.
          </div>
        )}
      </div>

      <div className="mt-4">
        <ThinRule />
      </div>

      <div className="mt-4">
        <div className="archive-label mb-3">Honours &amp; Medals</div>
        {character.badges.length > 0 ? (
          <div className="flex flex-wrap gap-1.5">
            {character.badges.map((badge, index) => (
              <span
                key={`${badge.name || "badge"}-${index}`}
                className="border border-[#c8a84b]/35 bg-[#c8a84b]/8 px-2 py-1 font-display text-[0.6rem] uppercase tracking-[0.14em] text-[#c8a84b]"
                title={badge.description || badge.name}
              >
                {badge.icon ? `${badge.icon} ` : ""}
                {badge.name || "Medal"}
              </span>
            ))}
          </div>
        ) : (
          <div className="text-sm text-[#746a5a]">No honours recorded.</div>
        )}
      </div>
    </aside>
  );

  return characterHref ? (
    <Link to={characterHref} className="block h-full transition hover:brightness-110">
      {panel}
    </Link>
  ) : (
    panel
  );
}

function PostActionButton({
  tone,
  onClick,
  children,
}: {
  tone: "approve" | "edit" | "delete";
  onClick: () => void;
  children: ReactNode;
}) {
  const tones = {
    approve: "border-emerald-400/25 text-emerald-300 hover:border-emerald-400/55 hover:bg-emerald-400/10",
    edit: "border-[#c8a84b]/25 text-[#c8a84b] hover:border-[#c8a84b]/55 hover:bg-[#c8a84b]/10",
    delete: "border-rose-500/25 text-rose-300 hover:border-rose-500/55 hover:bg-rose-500/10",
  };

  return (
    <button
      type="button"
      onClick={onClick}
      className={`inline-flex items-center gap-1.5 border bg-[#100f0d] px-3 py-1.5 font-display text-[0.68rem] uppercase tracking-[0.15em] transition ${tones[tone]}`}
    >
      {children}
    </button>
  );
}

function ReplyToolbar() {
  const tools = [
    { icon: Bold, label: "Bold" },
    { icon: Italic, label: "Italic" },
    { icon: Underline, label: "Underline" },
    { icon: List, label: "List" },
    { icon: Quote, label: "Quote" },
  ];

  return (
    <div className="flex items-center gap-1 border-b border-[#c8a84b]/10 px-4 py-2">
      {tools.map(({ icon: Icon, label }) => (
        <button
          key={label}
          type="button"
          title={label}
          className="p-1.5 text-[#746a5a] transition hover:bg-[#c8a84b]/8 hover:text-[#c8a84b]"
        >
          <Icon className="h-3.5 w-3.5" />
        </button>
      ))}
    </div>
  );
}

export function ThreadPage() {
  const { threadId } = useParams();
  const navigate = useNavigate();
  const [data, setData] = useState<ThreadApiResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [replyContent, setReplyContent] = useState("");
  const [submitMsg, setSubmitMsg] = useState<string | null>(null);
  const [editingPostId, setEditingPostId] = useState<string | number | null>(null);
  const [editContent, setEditContent] = useState("");

  const loadThread = async () => {
    if (!threadId) return;
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(`/api/threads/${threadId}/posts`);
      if (!res.ok) throw new Error(await res.text() || "Unable to load thread");
      setData(await res.json());
    } catch (e) {
      setError((e as Error).message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!threadId) {
      setError("Thread not found");
      setLoading(false);
      return;
    }
    loadThread();
  }, [threadId]);

  const participants = useMemo(() => {
    if (!data) return [];
    const seen = new Map<string | number, NonNullable<ThreadPost["character"]>>();
    data.posts.filter((p) => p.status === "approved").forEach((p) => {
      const character = p.character;
      const id = character?.id ?? `g-${p.id}`;
      if (!seen.has(id)) seen.set(id, character ?? { id: null, name: "Unknown Character" });
    });
    return Array.from(seen.values());
  }, [data]);

  const handleReply = async () => {
    if (!threadId || !replyContent.trim()) {
      setSubmitMsg("Please write a reply before posting.");
      return;
    }
    const res = await fetch(`/api/threads/${threadId}/posts`, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": getCsrf() },
      body: JSON.stringify({ content: replyContent }),
    });
    const result = await res.json();
    setSubmitMsg(result.message || (res.ok ? "Reply submitted." : "Unable to submit reply."));
    if (res.ok) {
      setReplyContent("");
      await loadThread();
    }
  };

  const handleApprovePost = async (postId: string | number) => {
    const res = await fetch(`/api/posts/${postId}/approve`, {
      method: "POST",
      headers: { "X-CSRF-TOKEN": getCsrf() },
    });
    if (res.ok) await loadThread();
  };

  const handleDeletePost = async (postId: string | number) => {
    if (!confirm("Delete this post?")) return;
    const res = await fetch(`/api/posts/${postId}`, {
      method: "DELETE",
      headers: { "X-CSRF-TOKEN": getCsrf() },
    });
    if (res.ok) await loadThread();
  };

  const startEditPost = (post: ThreadPost) => {
    setEditingPostId(post.id);
    setEditContent(post.content);
  };

  const saveEditPost = async () => {
    if (!editingPostId) return;
    const res = await fetch(`/api/posts/${editingPostId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": getCsrf() },
      body: JSON.stringify({ content: editContent }),
    });
    if (res.ok) {
      setEditingPostId(null);
      await loadThread();
    }
  };

  if (loading) {
    return (
      <div className="vaelthorn-page mx-auto max-w-[1180px] px-6 py-20 text-center font-chronicle text-lg text-[#c4b898]">
        Opening the chronicle...
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="vaelthorn-page mx-auto max-w-3xl px-6 py-20 text-center">
        <div className="archive-panel corner-ornaments p-10 text-[#c4b898]">{error || "Thread not found"}</div>
      </div>
    );
  }

  const { thread, posts, viewer } = data;
  const canPost = !["locked", "archived", "rejected"].includes(thread.status);
  const approvedPostCount = posts.filter((p) => p.status === "approved").length;
  const shouldShowCouncilNotice =
    (thread.status === "request_edit" || thread.status === "rejected" || thread.status === "pending") &&
    viewer.user_id === thread.user_id;

  return (
    <div className="vaelthorn-page mx-auto max-w-[1180px] px-4 py-8 sm:px-6">
      <button onClick={() => navigate(-1)} className="group mb-6 flex items-center gap-2">
        <ArrowLeft className="h-3.5 w-3.5 text-[#746a5a] transition group-hover:text-[#c8a84b]" />
        <span className="font-display text-xs uppercase tracking-[0.2em] text-[#746a5a] transition group-hover:text-[#c8a84b]">
          Back to {thread.city.name}
        </span>
      </button>

      <section className="archive-panel corner-ornaments mb-6 overflow-hidden p-6 sm:p-7">
        <div className="flex flex-wrap items-start justify-between gap-6">
          <div className="min-w-0 flex-1">
            <h1 className="font-decorative text-2xl font-bold leading-tight tracking-wide text-[#c8a84b] [text-shadow:0_0_28px_rgba(200,168,75,0.28)] sm:text-3xl">
              {thread.title}
            </h1>
            <div className="mt-4 flex flex-wrap items-center gap-4 text-[#746a5a]">
              <ThreadStatusBadge status={thread.status} label={thread.status_label} />
              <span className="inline-flex items-center gap-1.5 font-display text-xs tracking-wider">
                <MapPin className="h-3 w-3" />
                {thread.city.name}
              </span>
              <span className="inline-flex items-center gap-1.5 font-display text-xs tracking-wider">
                <Scroll className="h-3 w-3" />
                {approvedPostCount} approved posts
              </span>
            </div>
          </div>
        </div>

        {shouldShowCouncilNotice && (
          <div className="mt-6 flex items-start gap-3 border border-amber-400/25 bg-amber-400/5 px-4 py-3">
            <Zap className="mt-1 h-4 w-4 shrink-0 text-amber-300/80" />
            <div>
              <div className="font-display text-xs uppercase tracking-[0.18em] text-amber-300/90">Council Notice</div>
              <p className="font-chronicle mt-1 text-base leading-relaxed text-[#c4b898]/85">
                {thread.moderation_message || "This chronicle is waiting for Council approval."}
              </p>
            </div>
          </div>
        )}
      </section>

      <div className="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
        <main className="space-y-6">
          <div className="space-y-5">
            {posts.map((post, index) => {
              const character = normalizeCharacter(post);

              return (
                <article
                  key={post.id}
                  className={`overflow-hidden border border-[#c8a84b]/18 bg-[#0b0a08] shadow-[0_8px_38px_rgba(0,0,0,0.42)] ${
                    post.status === "pending" ? "ring-1 ring-amber-400/20" : ""
                  }`}
                >
                  <div className="grid min-h-[420px] lg:grid-cols-[278px_minmax(0,1fr)]">
                    <PostCharacterPanel post={post} />

                    <section className="flex min-w-0 flex-col bg-[linear-gradient(180deg,#11100e_0%,#0c0b09_100%)]">
                      <header className="flex flex-wrap items-center justify-between gap-4 border-b border-[#c8a84b]/10 bg-[#c8a84b]/[0.025] px-5 py-4 sm:px-6">
                        <div className="min-w-0">
                          <div className="flex flex-wrap items-center gap-3">
                            <span className="font-display text-base font-semibold tracking-wide text-[#c8a84b]">
                              {character.name}
                            </span>
                            <div className="h-3 w-px bg-[#c8a84b]/20" />
                            <span className="font-display text-xs uppercase tracking-[0.16em] text-[#746a5a]">
                              {character.rank}
                            </span>
                          </div>
                          <div className="mt-2 flex flex-wrap items-center gap-3 text-[#746a5a]">
                            <span className="inline-flex items-center gap-1.5 font-display text-xs tracking-wider">
                              <MapPin className="h-3 w-3" />
                              {character.kingdom}
                            </span>
                            <span className="inline-flex items-center gap-1.5 font-display text-xs tracking-wider">
                              <Clock className="h-3 w-3" />
                              {new Date(post.created_at).toLocaleString()}
                            </span>
                            <span className="font-display text-xs tracking-wider text-[#c8a84b]/30">#{index + 1}</span>
                          </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                          <ThreadStatusBadge status={post.status} />
                          {post.can_approve && (
                            <PostActionButton tone="approve" onClick={() => handleApprovePost(post.id)}>
                              <CheckCircle className="h-3.5 w-3.5" />
                              Approve
                            </PostActionButton>
                          )}
                          {post.can_edit && editingPostId !== post.id && (
                            <PostActionButton tone="edit" onClick={() => startEditPost(post)}>
                              <Pencil className="h-3.5 w-3.5" />
                              Edit
                            </PostActionButton>
                          )}
                          {post.can_edit && (
                            <PostActionButton tone="delete" onClick={() => handleDeletePost(post.id)}>
                              <Trash2 className="h-3.5 w-3.5" />
                              Delete
                            </PostActionButton>
                          )}
                        </div>
                      </header>

                      <div className="flex-1 px-5 py-6 sm:px-6">
                        {editingPostId === post.id ? (
                          <div className="border border-[#c8a84b]/18 bg-[#100f0d] p-4">
                            <Textarea
                              value={editContent}
                              onChange={(e) => setEditContent(e.target.value)}
                              className="mb-3 min-h-[160px] resize-none border-[#c8a84b]/15 bg-[#0b0a08] font-chronicle text-base leading-relaxed text-[#efe7d2] placeholder:text-[#746a5a]"
                            />
                            <div className="flex flex-wrap gap-2">
                              <Button size="sm" className="rounded-none bg-[#c8a84b] text-[#090807] hover:bg-[#d4b96e]" onClick={saveEditPost}>
                                <Edit3 className="h-3.5 w-3.5" />
                                Save
                              </Button>
                              <Button
                                size="sm"
                                variant="outline"
                                className="rounded-none border-[#c8a84b]/20 bg-transparent text-[#c4b898] hover:bg-[#c8a84b]/8 hover:text-[#c8a84b]"
                                onClick={() => setEditingPostId(null)}
                              >
                                Cancel
                              </Button>
                            </div>
                          </div>
                        ) : (
                          <div
                            className="prose prose-invert max-w-none font-chronicle text-lg leading-[1.85] text-[#d0c8b0] prose-p:text-[#d0c8b0] prose-a:text-[#c8a84b] hover:prose-a:text-[#d4b96e]"
                            dangerouslySetInnerHTML={{ __html: post.content }}
                          />
                        )}
                      </div>
                    </section>
                  </div>
                </article>
              );
            })}

            {posts.length === 0 && (
              <div className="archive-panel-soft p-12 text-center font-chronicle text-lg text-[#c4b898]">
                No posts have been written in this chronicle yet.
              </div>
            )}
          </div>

          {canPost && (
            <section className="overflow-hidden border border-[#c8a84b]/22 bg-[linear-gradient(180deg,#141210_0%,#100f0d_100%)] shadow-[0_6px_32px_rgba(0,0,0,0.5),0_0_0_1px_rgba(200,168,75,0.04)]">
              <header className="flex flex-wrap items-center justify-between gap-4 border-b border-[#c8a84b]/12 bg-[#c8a84b]/[0.03] px-5 py-4 sm:px-6">
                <div className="flex items-center gap-3">
                  <div className="flex h-8 w-8 items-center justify-center border border-[#c8a84b]/35">
                    <FlameKindling className="h-4 w-4 text-[#c8a84b]/70" />
                  </div>
                  <div>
                    <h2 className="font-decorative text-base tracking-wider text-[#c8a84b]">Continue the Tale</h2>
                    <p className="font-display text-[0.65rem] uppercase tracking-[0.18em] text-[#746a5a]">
                      Write your reply in character
                    </p>
                  </div>
                </div>
                <div className="flex items-center gap-2 border border-[#c8a84b]/18 bg-[#c8a84b]/5 px-3 py-1.5">
                  <Crown className="h-3 w-3 text-[#c8a84b]/55" />
                  <span className="font-display text-xs tracking-wider text-[#c8a84b]/75">Council review required</span>
                </div>
              </header>

              <ReplyToolbar />

              <div className="px-5 py-5 sm:px-6">
                <Textarea
                  value={replyContent}
                  onChange={(e) => setReplyContent(e.target.value)}
                  placeholder="Write your reply here..."
                  className="min-h-[190px] resize-none border-0 bg-transparent p-0 font-chronicle text-lg leading-[1.85] text-[#d0c8b0] shadow-none placeholder:text-[#746a5a]/75 focus-visible:ring-0"
                />
              </div>

              <footer className="flex flex-wrap items-center justify-between gap-4 border-t border-[#c8a84b]/10 px-5 py-4 sm:px-6">
                <div className="flex items-center gap-2 text-[#746a5a]">
                  <Sparkles className="h-3.5 w-3.5 text-amber-300/45" />
                  <span className="font-display text-xs tracking-wider">Your reply appears after Council approval.</span>
                </div>
                <div className="flex items-center gap-4">
                  {submitMsg && <span className="font-chronicle text-sm text-[#c4b898]">{submitMsg}</span>}
                  <Button
                    className="rounded-none bg-[linear-gradient(135deg,#c8a84b_0%,#8b6914_100%)] px-6 py-2.5 font-display text-xs uppercase tracking-[0.15em] text-[#090807] shadow-[0_0_18px_rgba(200,168,75,0.22),0_2px_8px_rgba(0,0,0,0.4)] hover:brightness-110"
                    onClick={handleReply}
                  >
                    <Send className="mr-1 h-4 w-4" />
                    Post Reply
                  </Button>
                </div>
              </footer>
            </section>
          )}

          {thread.status === "locked" && (
            <div className="archive-panel-soft p-6 text-center text-sm text-slate-300">This chronicle is locked. Replies are closed.</div>
          )}
          {thread.status === "archived" && (
            <div className="archive-panel-soft p-6 text-center text-sm text-indigo-300">This chronicle has been archived for reading only.</div>
          )}
        </main>

        <aside className="space-y-6">
          <CharacterModule />

          {participants.length > 0 && (
            <section className="archive-panel-soft p-6">
              <h3 className="font-display mb-4 text-lg text-[#c8a84b]">Participants</h3>
              <div className="space-y-3">
                {participants.map((char, index) => {
                  const name = char.name || "Unknown Character";
                  return (
                    <Link
                      key={`${char.id || "unknown"}-${index}`}
                      to={char.id ? `/character/${char.id}` : "#"}
                      className="group flex items-center gap-3 border border-[#c8a84b]/12 bg-[#131210] p-3 transition hover:border-[#c8a84b]/40"
                    >
                      <div className="flex h-11 w-11 items-center justify-center rounded-full border border-[#c8a84b]/45 bg-[#0e0c0a]">
                        <span className="font-display text-sm text-[#c8a84b]">{name.charAt(0).toUpperCase()}</span>
                      </div>
                      <div className="min-w-0">
                        <div className="truncate text-sm text-[#efe7d2]">{name}</div>
                        <div className="truncate text-xs text-[#746a5a]">{char.kingdom || "Unknown Kingdom"}</div>
                      </div>
                    </Link>
                  );
                })}
              </div>
            </section>
          )}
        </aside>
      </div>
    </div>
  );
}
