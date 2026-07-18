import { useState } from "react";
import { Link } from "react-router";
import { Pencil, Package } from "lucide-react";
import { characters, inventoryItems } from "../data/mockData";
import { ImageWithFallback } from "./figma/ImageWithFallback";

const RARITY_COLORS: Record<string, string> = {
  common: "#746a5a",
  uncommon: "#5a8c5a",
  rare: "#5a7abf",
  epic: "#9b5abf",
  legendary: "#c8a84b",
};

export function CharacterModule() {
  const [collapsed, setCollapsed] = useState(false);
  const character = characters.aelric;

  const charInventory = inventoryItems.filter((item) =>
    character.inventory.includes(item.id)
  );

  const stats = [
    { label: "STR", value: character.stats.strength, max: 25, color: "#c8a84b" },
    { label: "AGI", value: character.stats.agility, max: 25, color: "#6890c8" },
    { label: "HP", value: character.stats.hp, max: 100, color: "#c84848" },
    { label: "MANA", value: character.stats.mana, max: 150, color: "#9b8fc8" },
    { label: "INT", value: character.stats.intelligence, max: 25, color: "#5a8c6a" },
  ];

  if (collapsed) {
    return (
      <div className="fixed right-6 top-20 z-40">
        <Link
          to={`/character/${character.id}`}
          onClick={() => setCollapsed(false)}
          className="flex items-center gap-3 rounded-full border bg-[#141210]/95 px-4 py-2 shadow-[0_10px_30px_rgba(0,0,0,0.35)] backdrop-blur transition-all hover:bg-[#1a1713]"
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
      <div className="overflow-hidden border border-[#c8a84b]/20 bg-[#0e0c09]">
        {/* Header */}
        <div className="flex items-center justify-between border-b border-[#c8a84b]/15 px-5 py-3">
          <div className="flex items-center gap-2">
            <span className="text-[10px] text-[#c8a84b]/40">♦</span>
            <span className="font-display text-[10px] tracking-[0.25em] text-[#c8a84b]/70 uppercase">
              My Character
            </span>
          </div>
          <Link to={`/character/${character.id}`}>
            <Pencil className="h-3.5 w-3.5 text-[#c8a84b]/40 hover:text-[#c8a84b] transition-colors" />
          </Link>
        </div>

        {/* Avatar */}
        <div className="flex flex-col items-center py-7">
          <div className="relative">
            {/* Outer ring */}
            <div
              className="flex h-[90px] w-[90px] items-center justify-center rounded-full border-2"
              style={{ borderColor: "#c8a84b40" }}
            >
              {/* Inner ring */}
              <div
                className="flex h-[78px] w-[78px] items-center justify-center rounded-full border"
                style={{ borderColor: "#c8a84b25" }}
              >
                <div className="h-[68px] w-[68px] overflow-hidden rounded-full">
                  <ImageWithFallback
                    src="https://images.unsplash.com/photo-1473997094636-80cc50db3e0c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkYXJrJTIwZmFudGFzeSUyMHdhcnJpb3IlMjBwb3J0cmFpdHxlbnwxfHx8fDE3ODEzNDM0MTF8MA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
                    alt={character.name}
                    className="h-full w-full object-cover"
                  />
                </div>
              </div>
            </div>
            {/* Corner ornaments */}
            <span className="absolute -top-1 -left-1 text-[8px] text-[#c8a84b]/30">◆</span>
            <span className="absolute -top-1 -right-1 text-[8px] text-[#c8a84b]/30">◆</span>
            <span className="absolute -bottom-1 -left-1 text-[8px] text-[#c8a84b]/30">◆</span>
            <span className="absolute -bottom-1 -right-1 text-[8px] text-[#c8a84b]/30">◆</span>
          </div>

          <h3 className="font-display mt-4 text-base tracking-[0.12em] text-[#efe7d2]">
            {character.name}
          </h3>
          <p className="mt-0.5 text-[10px] tracking-[0.3em] text-[#c8a84b]/55 uppercase">
            {character.role}
          </p>
          <span className="mt-2 text-[10px] text-[#c8a84b]/25">◆</span>
        </div>

        {/* Info Rows */}
        <div className="border-t border-b border-[#c8a84b]/10">
          {[
            { label: "KINGDOM", value: character.kingdom },
            { label: "LOCATION", value: character.location },
            { label: "RANK", value: character.rank },
            { label: "POSTS", value: `${character.posts} chronicles` },
          ].map(({ label, value }, i) => (
            <div
              key={label}
              className={`flex items-center justify-between px-5 py-2.5 ${i !== 0 ? "border-t border-[#c8a84b]/08" : ""}`}
            >
              <span className="text-[10px] tracking-[0.18em] text-[#746a5a]">{label}</span>
              <span className="text-xs text-[#efe7d2]">{value}</span>
            </div>
          ))}
        </div>

        {/* Honours & Medals */}
        <div className="px-5 py-4">
          <p className="mb-2 text-[10px] tracking-[0.18em] text-[#c8a84b]/50 uppercase">
            Honours &amp; Medals
          </p>
          <p className="text-xs italic text-[#746a5a]">No honours recorded.</p>
        </div>

        {/* Divider */}
        <div className="mx-5 border-t border-[#c8a84b]/10" />
        <div className="flex justify-center py-1.5">
          <span className="text-[9px] text-[#c8a84b]/20">◆</span>
        </div>

        {/* Attributes */}
        <div className="px-5 pb-4">
          <p className="mb-3 text-[10px] tracking-[0.18em] text-[#c8a84b]/50 uppercase">
            Attributes
          </p>
          <div className="space-y-2.5">
            {stats.map(({ label, value, max, color }) => (
              <div key={label} className="flex items-center gap-3">
                <span className="w-7 text-[9px] tracking-wider text-[#746a5a]">{label}</span>
                <div className="flex-1 h-[3px] overflow-hidden rounded-full bg-[#1a1713]">
                  <div
                    className="h-full rounded-full transition-all"
                    style={{
                      width: `${Math.min((value / max) * 100, 100)}%`,
                      backgroundColor: color,
                    }}
                  />
                </div>
                <span className="w-7 text-right text-[10px] text-[#746a5a]">{value}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Divider */}
        <div className="mx-5 border-t border-[#c8a84b]/10" />
        <div className="flex justify-center py-1.5">
          <span className="text-[9px] text-[#c8a84b]/20">◆</span>
        </div>

        {/* Inventory Grid */}
        <div className="px-5 pb-5">
          <p className="mb-3 text-[10px] tracking-[0.18em] text-[#c8a84b]/50 uppercase">
            Inventory
          </p>
          <div className="grid grid-cols-3 gap-2">
            {Array.from({ length: 9 }).map((_, i) => {
              const item = charInventory[i];
              return (
                <div
                  key={i}
                  className="group relative aspect-square rounded-sm border border-[#c8a84b]/12 bg-[#141210] flex items-center justify-center cursor-pointer hover:border-[#c8a84b]/35 transition-colors"
                >
                  {item ? (
                    <>
                      <span className="text-xl">{item.icon}</span>
                      {/* Rarity indicator dot */}
                      <span
                        className="absolute bottom-0.5 right-0.5 h-1.5 w-1.5 rounded-full"
                        style={{ backgroundColor: RARITY_COLORS[item.rarity] }}
                      />
                      {/* Tooltip */}
                      <div className="pointer-events-none absolute bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap rounded bg-[#1a1713] border border-[#c8a84b]/20 px-2 py-1 text-[9px] text-[#efe7d2] opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        {item.name}
                        {item.quantity > 1 && (
                          <span className="ml-1 text-[#c8a84b]">×{item.quantity}</span>
                        )}
                      </div>
                    </>
                  ) : (
                    <Package className="h-4 w-4 text-[#c8a84b]/08" />
                  )}
                </div>
              );
            })}
          </div>
          <Link
            to="/character/aelric"
            className="mt-3 block text-center text-[9px] tracking-[0.15em] text-[#c8a84b]/35 hover:text-[#c8a84b]/60 uppercase transition-colors"
          >
            View All →
          </Link>
        </div>
      </div>
    </div>
  );
}
