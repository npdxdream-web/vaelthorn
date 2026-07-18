import { useState, useEffect } from "react";
import { Link } from "react-router";
import { Swords, Sparkles, Brain, Zap } from "lucide-react";
import { characters } from "../data/mockData";
import { ImageWithFallback } from "./figma/ImageWithFallback";

export function CharacterModule() {
  const [collapsed, setCollapsed] = useState(false);
  const character = characters.aelric;

  useEffect(() => {
    const handleScroll = () => {
      setCollapsed(window.scrollY > 100);
    };

    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  if (collapsed) {
    return (
      <div className="fixed right-6 top-20 z-40">
        <Link 
          to={`/character/${character.id}`}
          className="flex items-center gap-3 rounded-full border-2 bg-[#1a1a1a] px-4 py-2 transition-all hover:bg-[#2a2a2a]"
          style={{ borderColor: character.cityColor }}
        >
          <div 
            className="flex h-10 w-10 items-center justify-center rounded-full border-2 bg-gradient-to-br from-[#7a8c9e] to-[#5a6c7e]"
            style={{ borderColor: character.cityColor }}
          >
            <span className="text-sm text-[#e8e6e3]">A</span>
          </div>
          <div className="flex flex-col">
            <span className="text-sm font-medium text-[#e8e6e3]">{character.name}</span>
            <span className="text-xs text-[#a8a6a3]">Lv. {character.level}</span>
          </div>
        </Link>
      </div>
    );
  }

  return (
    <div className="sticky top-20">
      <div className="glow-gold overflow-hidden rounded-xl border border-[#2a2a2a] bg-[#1a1a1a]">
        {/* Header */}
        <div 
          className="border-b px-6 py-4"
          style={{ borderColor: character.cityColor + '40', backgroundColor: character.cityColor + '10' }}
        >
          <Link to={`/character/${character.id}`} className="group">
            <div className="flex items-center gap-4">
              <div 
                className="flex h-16 w-16 items-center justify-center rounded-full border-3 bg-gradient-to-br from-[#7a8c9e] to-[#5a6c7e] transition-transform group-hover:scale-105"
                style={{ borderColor: character.cityColor }}
              >
                <ImageWithFallback 
                  src="https://images.unsplash.com/photo-1473997094636-80cc50db3e0c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkYXJrJTIwZmFudGFzeSUyMHdhcnJpb3IlMjBwb3J0cmFpdHxlbnwxfHx8fDE3ODEzNDM0MTF8MA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
                  alt={character.name}
                  className="h-full w-full rounded-full object-cover"
                />
              </div>
              <div className="flex flex-col">
                <h3 className="font-display text-[#D4AF37]">{character.name}</h3>
                <p className="text-sm text-[#a8a6a3]">{character.class}</p>
              </div>
            </div>
          </Link>
        </div>

        {/* Stats */}
        <div className="p-6">
          <div className="mb-4 flex items-center justify-between">
            <span className="text-sm text-[#a8a6a3]">Level</span>
            <span className="font-display text-lg text-[#D4AF37]">{character.level}</span>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <StatBox 
              icon={<Swords className="h-4 w-4" />}
              label="STR"
              value={character.stats.strength}
            />
            <StatBox 
              icon={<Brain className="h-4 w-4" />}
              label="INT"
              value={character.stats.intelligence}
            />
            <StatBox 
              icon={<Sparkles className="h-4 w-4" />}
              label="WIS"
              value={character.stats.wisdom}
            />
            <StatBox 
              icon={<Zap className="h-4 w-4" />}
              label="DEX"
              value={character.stats.dexterity}
            />
          </div>

          <div className="mt-6 space-y-2 border-t border-[#2a2a2a] pt-4">
            <div className="flex justify-between text-sm">
              <span className="text-[#a8a6a3]">Posts</span>
              <span className="text-[#e8e6e3]">{character.posts}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-[#a8a6a3]">City</span>
              <span style={{ color: character.cityColor }}>{character.cityName}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-[#a8a6a3]">Joined</span>
              <span className="text-[#e8e6e3]">{character.joined}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function StatBox({ icon, label, value }: { icon: React.ReactNode; label: string; value: number }) {
  return (
    <div className="flex items-center gap-2 rounded-lg border border-[#2a2a2a] bg-[#141414] p-3">
      <div className="text-[#B87333]">{icon}</div>
      <div className="flex flex-col">
        <span className="text-xs text-[#a8a6a3]">{label}</span>
        <span className="text-[#e8e6e3]">{value}</span>
      </div>
    </div>
  );
}
