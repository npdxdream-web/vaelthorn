import { useParams, Link } from "react-router";
import { ArrowLeft, Scroll, MapPin, Calendar } from "lucide-react";
import { characters, threads } from "../data/mockData";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";

export function CharacterPage() {
  const { characterId } = useParams();
  const character = characters[characterId as keyof typeof characters] || characters.aelric;
  
  const characterThreads = threads.filter(t => t.author.id === character.id);

  return (
    <div className="vaelthorn-page mx-auto max-w-5xl px-6 py-12">
      <Link 
        to="/"
        className="mb-6 inline-flex items-center gap-2 text-sm text-[#a8a6a3] hover:text-[#D4AF37]"
      >
        <ArrowLeft className="h-4 w-4" />
        Back to Home
      </Link>

      {/* Character Header */}
      <div className="glow-gold-strong mb-8 overflow-hidden rounded-xl border-2 border-[#D4AF37]/50 bg-[#1a1a1a]">
        <div
          className="border-b px-8 py-6"
          style={{
            borderColor: character.kingdomColor + '40',
            background: `linear-gradient(135deg, ${character.kingdomColor}15, transparent)`
          }}
        >
          <div className="flex items-start gap-6">
            <div
              className="flex h-32 w-32 items-center justify-center rounded-full border-4 bg-gradient-to-br p-1"
              style={{
                borderColor: character.kingdomColor,
                backgroundImage: `linear-gradient(135deg, ${character.kingdomColor}, ${character.kingdomColor}80)`
              }}
            >
              <div className="flex h-full w-full items-center justify-center overflow-hidden rounded-full bg-[#1a1a1a]">
                <ImageWithFallback 
                  src="https://images.unsplash.com/photo-1473997094636-80cc50db3e0c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkYXJrJTIwZmFudGFzeSUyMHdhcnJpb3IlMjBwb3J0cmFpdHxlbnwxfHx8fDE3ODEzNDM0MTF8MA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
                  alt={character.name}
                  className="h-full w-full object-cover"
                />
              </div>
            </div>
            <div className="flex-1">
              <h1 className="font-display mb-2 text-4xl tracking-wide text-[#D4AF37]">
                {character.name}
              </h1>
              <div className="mb-4 flex items-center gap-4 text-lg">
                <span className="text-[#e8e6e3]">{character.class}</span>
                <span className="text-[#686664]">•</span>
                <span className="text-[#a8a6a3]">{character.race}</span>
                <span className="text-[#686664]">•</span>
                <span className="font-display text-[#D4AF37]">Level {character.level}</span>
              </div>
              <p className="text-[#a8a6a3]">{character.bio}</p>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-4 border-b border-[#2a2a2a] bg-[#141414]">
          <StatDisplay label="Strength" value={character.stats.strength} />
          <StatDisplay label="Intelligence" value={character.stats.intelligence} />
          <StatDisplay label="Wisdom" value={character.stats.wisdom} />
          <StatDisplay label="Dexterity" value={character.stats.dexterity} />
        </div>

        {/* Info Bar */}
        <div className="grid grid-cols-3 px-8 py-6">
          <div className="flex items-center gap-3">
            <MapPin className="h-5 w-5 text-[#B87333]" />
            <div>
              <div className="text-xs text-[#686664]">Home Kingdom</div>
              <div style={{ color: character.kingdomColor }}>{character.kingdomName}</div>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <Scroll className="h-5 w-5 text-[#B87333]" />
            <div>
              <div className="text-xs text-[#686664]">Posts</div>
              <div className="text-[#e8e6e3]">{character.posts}</div>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <Calendar className="h-5 w-5 text-[#B87333]" />
            <div>
              <div className="text-xs text-[#686664]">Joined</div>
              <div className="text-[#e8e6e3]">{character.joined}</div>
            </div>
          </div>
        </div>
      </div>

      {/* Recent Tales */}
      <div>
        <h2 className="font-display mb-4 text-2xl text-[#D4AF37]">Recent Tales</h2>
        <div className="space-y-4">
          {characterThreads.length > 0 ? (
            characterThreads.map((thread) => (
              <Link 
                key={thread.id}
                to={`/thread/${thread.id}`}
                className="group block rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-6 transition-all hover:border-[#D4AF37]"
              >
                <h3 className="mb-2 text-lg font-medium text-[#e8e6e3] group-hover:text-[#D4AF37]">
                  {thread.title}
                </h3>
                <div className="flex items-center gap-3 text-sm text-[#a8a6a3]">
                  <span>{thread.city}</span>
                  <span>•</span>
                  <span>{thread.replies} replies</span>
                  <span>•</span>
                  <span>{thread.lastPost}</span>
                </div>
              </Link>
            ))
          ) : (
            <div className="rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-12 text-center">
              <Scroll className="mx-auto mb-4 h-12 w-12 text-[#686664]" />
              <p className="text-[#a8a6a3]">No tales yet. Start your journey!</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function StatDisplay({ label, value }: { label: string; value: number }) {
  const percentage = (value / 25) * 100; // Assuming max stat is 25
  
  return (
    <div className="border-r border-[#2a2a2a] px-6 py-4 last:border-r-0">
      <div className="mb-2 text-xs text-[#686664]">{label}</div>
      <div className="mb-2 font-display text-2xl text-[#D4AF37]">{value}</div>
      <div className="h-1 overflow-hidden rounded-full bg-[#2a2a2a]">
        <div 
          className="h-full rounded-full bg-gradient-to-r from-[#D4AF37] to-[#B87333]"
          style={{ width: `${percentage}%` }}
        />
      </div>
    </div>
  );
}
