import { useEffect, useState } from "react";
import { useParams, Link } from "react-router";
import { MessageSquare, Clock, Tag } from "lucide-react";
import { CharacterModule } from "../components/CharacterModule";

const STATUS_COLORS: Record<string, string> = {
  approved: "text-emerald-400 bg-emerald-950/30 border-emerald-400/20",
  pending:  "text-amber-300 bg-amber-950/30 border-amber-400/20",
  draft:    "text-slate-400 bg-slate-950/30 border-slate-400/20",
  request_edit: "text-orange-400 bg-orange-950/30 border-orange-400/20",
  rejected: "text-rose-400 bg-rose-950/30 border-rose-400/20",
  locked:   "text-slate-300 bg-slate-800/30 border-slate-500/20",
  archived: "text-indigo-300 bg-indigo-950/30 border-indigo-400/20",
};

interface CityApiResponse {
  id: string | number;
  name: string;
  description?: string;
  kingdom: {
    id: string | number;
    name: string;
    color?: string;
    icon?: string;
  };
  threads: Array<{
    id: string | number;
    title: string;
    status: string;
    status_label: string;
    author: { id: string | number | null; name: string };
    tags: string[];
    replies: number;
    lastActivity: string;
    kingdomName?: string;
  }>;
}

const DEFAULT_AUTHOR_COLOR = "#7a8c9e";

export function CityPage() {
  const { cityId } = useParams();
  const [city, setCity] = useState<CityApiResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!cityId) {
      setError("City not found");
      setLoading(false);
      return;
    }

    setLoading(true);
    setError(null);

    fetch(`/api/cities/${cityId}`)
      .then(async (response) => {
        if (!response.ok) {
          const message = await response.text();
          throw new Error(message || "Unable to load city");
        }
        return response.json();
      })
      .then((data: CityApiResponse) => {
        setCity(data);
      })
      .catch((fetchError) => {
        setError(fetchError.message || "Unable to load city");
      })
      .finally(() => {
        setLoading(false);
      });
  }, [cityId]);

  return (
    <div className="vaelthorn-page mx-auto max-w-7xl px-6 py-8">
      {loading ? (
        <div className="text-center text-[#e8e6e3]">Loading city...</div>
      ) : error ? (
        <div className="rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-8 text-center text-[#e8e6e3]">
          <p>{error}</p>
        </div>
      ) : city ? (
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
          <div className="lg:col-span-2">
            <div className="mb-6">
              <div className="mb-2 flex items-center gap-2 text-sm text-[#a8a6a3]">
                <Link to="/" className="hover:text-[#D4AF37]">Thiran</Link>
                <span>/</span>
                <span style={{ color: city.kingdom.color || "#D4AF37" }}>{city.kingdom.name}</span>
                <span>/</span>
                <span className="text-[#e8e6e3]">{city.name}</span>
              </div>
              <h1 className="font-display mb-4 text-3xl tracking-wide text-[#D4AF37]">
                {city.name}
              </h1>
              <div className="flex items-center gap-3">
                <a href={`/cities/${city.id}/threads/create`}
                  className="inline-flex items-center gap-2 rounded-lg bg-[#D4AF37] px-4 py-2 text-sm font-medium text-[#0f0f0f] hover:bg-[#B8941F]">
                  + Start New Tale
                </a>
              </div>
            </div>

            <div className="space-y-4">
              {city.threads.map((thread) => (
                <Link
                  key={thread.id}
                  to={`/thread/${thread.id}`}
                  className="group block overflow-hidden rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] transition-all hover:border-[#D4AF37]"
                >
                  <div className="p-5">
                    <div className="mb-3 flex items-start justify-between gap-4">
                      <div className="flex-1 min-w-0">
                        <div className="mb-1 flex flex-wrap items-center gap-2">
                          <h2 className="font-medium text-[#e8e6e3] group-hover:text-[#D4AF37] truncate">
                            {thread.title}
                          </h2>
                          <span className={`shrink-0 rounded-full border px-2 py-0.5 text-xs ${STATUS_COLORS[thread.status] || STATUS_COLORS.pending}`}>
                            {thread.status_label}
                          </span>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-[#a8a6a3]">
                          <span>by {thread.author.name}</span>
                          <span>•</span>
                          <span>{thread.kingdomName || city.kingdom.name}</span>
                        </div>
                      </div>
                      <div className="flex flex-col items-end gap-1">
                        <div className="flex items-center gap-1 text-sm text-[#a8a6a3]">
                          <Clock className="h-4 w-4" />
                          <span>{thread.lastActivity}</span>
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        {thread.tags.map((tag) => (
                          <span
                            key={tag}
                            className="flex items-center gap-1 rounded-full border border-[#2a2a2a] bg-[#141414] px-3 py-1 text-xs text-[#a8a6a3]"
                          >
                            <Tag className="h-3 w-3" />
                            {tag}
                          </span>
                        ))}
                      </div>
                      <div className="flex items-center gap-1 text-sm text-[#B87333]">
                        <MessageSquare className="h-4 w-4" />
                        <span>{thread.replies}</span>
                      </div>
                    </div>
                  </div>

                  <div
                    className="border-t px-5 py-3"
                    style={{
                      borderColor: DEFAULT_AUTHOR_COLOR + '40',
                      backgroundColor: DEFAULT_AUTHOR_COLOR + '08'
                    }}
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className="flex h-8 w-8 items-center justify-center rounded-full border-2"
                        style={{
                          borderColor: DEFAULT_AUTHOR_COLOR,
                          background: `linear-gradient(135deg, ${DEFAULT_AUTHOR_COLOR}aa, ${DEFAULT_AUTHOR_COLOR}66)`
                        }}
                      >
                        <span className="text-xs text-[#e8e6e3]">{thread.author.name[0]}</span>
                      </div>
                      <div className="flex items-center gap-2 text-xs text-[#a8a6a3]">
                        <span className="text-[#e8e6e3]">{thread.author.name}</span>
                        <span>•</span>
                        <span>{thread.kingdomName || city.kingdom.name}</span>
                      </div>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>

          <div className="lg:col-span-1">
            <CharacterModule />

            <div className="mt-6 rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-6">
              <h3 className="font-display mb-4 text-lg text-[#D4AF37]">City Info</h3>
              <div className="space-y-3 text-sm">
                <div className="flex justify-between">
                  <span className="text-[#a8a6a3]">Active Tales</span>
                  <span className="text-[#e8e6e3]">{city.threads.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-[#a8a6a3]">Location</span>
                  <span className="text-[#e8e6e3]">{city.kingdom.name}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-[#a8a6a3]">Description</span>
                  <span className="text-[#e8e6e3]">{city.description || "—"}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}
