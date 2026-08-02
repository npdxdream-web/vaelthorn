import { useState } from "react";
import {
  Home,
  Map,
  BookOpen,
  LogOut,
  ArrowLeft,
  Shield,
  Sword,
  Star,
  Crown,
  Flame,
  Bold,
  Italic,
  Underline,
  Link,
  List,
  Quote,
  CheckCircle,
  Edit3,
  Trash2,
  Clock,
  MapPin,
  ChevronRight,
  Scroll,
  Zap,
  Feather,
  FlameKindling,
} from "lucide-react";

// ─── Shared Primitives ────────────────────────────────────────────────────────

function GoldDivider() {
  return (
    <div className="flex items-center gap-3 w-full">
      <div className="flex-1 h-px bg-gradient-to-r from-transparent via-[#c8a84b]/30 to-[#c8a84b]/60" />
      <svg width="18" height="10" viewBox="0 0 18 10" fill="none">
        <path d="M9 1 L12 5 L9 9 L6 5 Z" fill="#c8a84b" opacity="0.75" />
        <line x1="0" y1="5" x2="5" y2="5" stroke="#c8a84b" strokeWidth="0.7" opacity="0.5" />
        <line x1="13" y1="5" x2="18" y2="5" stroke="#c8a84b" strokeWidth="0.7" opacity="0.5" />
      </svg>
      <div className="flex-1 h-px bg-gradient-to-l from-transparent via-[#c8a84b]/30 to-[#c8a84b]/60" />
    </div>
  );
}

// Fine horizontal rule with small diamond
function ThinRule() {
  return (
    <div className="flex items-center gap-2 w-full">
      <div className="flex-1 h-px bg-[#c8a84b]/15" />
      <div className="w-1 h-1 rotate-45 bg-[#c8a84b]/40" />
      <div className="flex-1 h-px bg-[#c8a84b]/15" />
    </div>
  );
}

function VaelthornLogo() {
  return (
    <div className="flex items-center gap-2.5">
      <svg viewBox="0 0 32 32" fill="none" className="w-8 h-8 flex-shrink-0">
        <polygon
          points="16,2 20,12 30,12 22,19 25,30 16,23 7,30 10,19 2,12 12,12"
          fill="none"
          stroke="#c8a84b"
          strokeWidth="1.1"
        />
        <polygon
          points="16,7 19,14 26,14 20.5,18 22.5,26 16,21.5 9.5,26 11.5,18 6,14 13,14"
          fill="#c8a84b"
          opacity="0.18"
        />
        <circle cx="16" cy="16" r="2.2" fill="#c8a84b" opacity="0.85" />
      </svg>
      <div>
        <div className="font-['Cinzel_Decorative'] text-[#c8a84b] text-[13px] font-bold tracking-widest leading-none">
          VAELTHORN
        </div>
        <div className="font-['Cinzel'] text-[#7a7060] text-[7px] tracking-[0.35em] leading-none mt-[3px] uppercase">
          Chronicles
        </div>
      </div>
    </div>
  );
}

// ─── Top Nav ──────────────────────────────────────────────────────────────────
function TopNav() {
  return (
    <header className="sticky top-0 z-50 w-full border-b border-[#c8a84b]/12 bg-[#090807]/96 backdrop-blur-sm">
      <div className="mx-auto flex h-14 max-w-[1440px] items-center justify-between px-8">
        <VaelthornLogo />
        <nav className="flex items-center gap-0.5">
          {[
            { icon: Home, label: "Home" },
            { icon: Map, label: "World Map" },
            { icon: BookOpen, label: "Chronicle" },
          ].map(({ icon: Icon, label }) => (
            <button
              key={label}
              title={label}
              className="flex items-center gap-2 px-4 py-2 font-['Cinzel'] text-[10px] text-[#6b6050] tracking-[0.18em] uppercase hover:text-[#c8a84b] hover:bg-[#c8a84b]/6 transition-all duration-200 group"
            >
              <Icon size={13} className="group-hover:text-[#c8a84b] transition-colors" />
              <span className="hidden md:block">{label}</span>
            </button>
          ))}
        </nav>
        <div className="flex items-center gap-3">
          <div className="relative cursor-pointer">
            <div className="w-9 h-9 rounded-full border-2 border-[#c8a84b]/35 bg-[#1a1713] overflow-hidden flex items-center justify-center hover:border-[#c8a84b]/65 transition-colors">
              <Crown size={15} className="text-[#c8a84b]/60" />
            </div>
            <div className="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-[#2d5a2d] rounded-full border border-[#090807]" />
          </div>
          <button className="flex items-center gap-1.5 px-3 py-1.5 border border-[#c8a84b]/12 text-[#6b6050] hover:text-[#c8a84b] hover:border-[#c8a84b]/28 transition-all font-['Cinzel'] text-[9px] tracking-wider uppercase">
            <LogOut size={11} />
            <span className="hidden sm:block">Depart</span>
          </button>
        </div>
      </div>
    </header>
  );
}

// ─── Status Badge ─────────────────────────────────────────────────────────────
function StatusBadge({ status }: { status: "Live" | "Pending" | "Approved" | "Locked" }) {
  const cfg = {
    Live:     { cls: "border-[#4ade80]/35 text-[#4ade80] bg-[#4ade80]/7",  dot: "#4ade80" },
    Pending:  { cls: "border-[#f59e0b]/35 text-[#f59e0b] bg-[#f59e0b]/7",  dot: "#f59e0b" },
    Approved: { cls: "border-[#c8a84b]/40 text-[#c8a84b] bg-[#c8a84b]/8",  dot: "#c8a84b" },
    Locked:   { cls: "border-[#6b7280]/35 text-[#6b7280] bg-[#6b7280]/8",  dot: "#6b7280" },
  }[status];
  return (
    <span
      className={`inline-flex items-center gap-1.5 px-2 py-[3px] border font-['Cinzel'] text-[8.5px] tracking-[0.14em] uppercase ${cfg.cls}`}
    >
      <span
        className="w-[5px] h-[5px] rounded-full flex-shrink-0"
        style={{ backgroundColor: cfg.dot, boxShadow: `0 0 4px ${cfg.dot}` }}
      />
      {status}
    </span>
  );
}

// ─── Badge Chip ───────────────────────────────────────────────────────────────
interface BadgeProps {
  label: string;
  tier: "gold" | "silver" | "emerald" | "crimson" | "muted";
}

function BadgeChip({ label, tier }: BadgeProps) {
  const palette = {
    gold:    { border: "rgba(200,168,75,0.55)",  text: "#c8a84b", bg: "rgba(200,168,75,0.08)" },
    silver:  { border: "rgba(180,185,195,0.45)", text: "#b4b9c3", bg: "rgba(180,185,195,0.06)" },
    emerald: { border: "rgba(52,168,100,0.45)",  text: "#34a864", bg: "rgba(52,168,100,0.07)" },
    crimson: { border: "rgba(168,52,52,0.45)",   text: "#a83434", bg: "rgba(168,52,52,0.07)" },
    muted:   { border: "rgba(100,92,80,0.4)",    text: "#7a7060", bg: "rgba(100,92,80,0.06)" },
  }[tier];
  return (
    <div
      className="inline-flex items-center px-2 py-[3px] font-['Cinzel'] text-[7.5px] tracking-[0.12em] uppercase"
      style={{ border: `1px solid ${palette.border}`, color: palette.text, background: palette.bg }}
    >
      {label}
    </div>
  );
}

// ─── Attribute Bar ────────────────────────────────────────────────────────────
function AttrBar({
  label,
  value,
  max = 100,
  color = "#c8a84b",
}: {
  label: string;
  value: number;
  max?: number;
  color?: string;
}) {
  const pct = Math.round((value / max) * 100);
  return (
    <div className="flex items-center gap-2">
      <span className="font-['Cinzel'] text-[8px] text-[#6b6050] tracking-wider w-6 flex-shrink-0">{label}</span>
      <div className="flex-1 h-[3px] bg-[#1e1c18] rounded-full overflow-hidden">
        <div
          className="h-full rounded-full"
          style={{
            width: `${pct}%`,
            background: `linear-gradient(90deg, ${color}60, ${color}cc)`,
            boxShadow: `0 0 4px ${color}50`,
          }}
        />
      </div>
      <span className="font-['Cinzel'] text-[8px] text-[#c8a84b]/50 w-5 text-right flex-shrink-0">{value}</span>
    </div>
  );
}

// ─── Portrait Frame ───────────────────────────────────────────────────────────
function PortraitFrame({ status }: { status: "Active" | "Away" | "Offline" }) {
  const dotColor = { Active: "#4ade80", Away: "#f59e0b", Offline: "#6b7280" }[status];
  const dotGlow  = { Active: "#4ade80", Away: "#f59e0b", Offline: "#6b7280" }[status];

  return (
    <div className="relative w-full">
      {/* Outer glow ring */}
      <div
        className="absolute -inset-[3px] rounded-sm pointer-events-none"
        style={{ boxShadow: "0 0 18px rgba(200,168,75,0.12), 0 0 6px rgba(200,168,75,0.08)" }}
      />

      {/* The frame border */}
      <div
        className="relative"
        style={{
          border: "1px solid rgba(200,168,75,0.45)",
          boxShadow: "inset 0 0 0 2px rgba(200,168,75,0.08), inset 0 0 24px rgba(0,0,0,0.55)",
        }}
      >
        {/* Tall portrait canvas */}
        <div
          className="relative w-full"
          style={{
            aspectRatio: "3/4.2",
            background: "linear-gradient(175deg, #1c1915 0%, #0d0c0a 60%, #090807 100%)",
          }}
        >
          {/* Subtle inner vignette */}
          <div
            className="absolute inset-0 pointer-events-none"
            style={{
              background:
                "radial-gradient(ellipse at 50% 35%, transparent 45%, rgba(0,0,0,0.55) 100%)",
            }}
          />

          {/* Placeholder silhouette — replace with <img> for real PNG */}
          <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 opacity-20">
            <div
              className="w-16 h-16 rounded-full border border-[#c8a84b]/60 flex items-center justify-center"
              style={{ boxShadow: "0 0 12px rgba(200,168,75,0.15)" }}
            >
              <Crown size={26} className="text-[#c8a84b]" />
            </div>
            <div className="flex flex-col items-center gap-1 opacity-80">
              <div className="w-10 h-[22px] border border-[#c8a84b]/40 rounded-sm" />
              <div className="w-16 h-12 border border-[#c8a84b]/30 rounded-sm" />
            </div>
          </div>

          {/* Bottom gradient fade for name bleed */}
          <div
            className="absolute inset-x-0 bottom-0 h-20 pointer-events-none"
            style={{
              background: "linear-gradient(to top, rgba(9,8,7,0.92) 0%, transparent 100%)",
            }}
          />

          {/* Decorative top filigree line */}
          <div className="absolute top-2.5 left-3 right-3 flex items-center gap-1.5 opacity-40">
            <div className="flex-1 h-px bg-[#c8a84b]" />
            <div className="w-1 h-1 rotate-45 bg-[#c8a84b]" />
            <div className="flex-1 h-px bg-[#c8a84b]" />
          </div>
          <div className="absolute bottom-2.5 left-3 right-3 flex items-center gap-1.5 opacity-30">
            <div className="flex-1 h-px bg-[#c8a84b]" />
            <div className="w-1 h-1 rotate-45 bg-[#c8a84b]" />
            <div className="flex-1 h-px bg-[#c8a84b]" />
          </div>

          {/* Status pill — top-right */}
          <div className="absolute top-3 right-3 flex items-center gap-1 px-1.5 py-0.5 bg-[#090807]/85 border border-[#c8a84b]/20">
            <div
              className="w-[5px] h-[5px] rounded-full"
              style={{ backgroundColor: dotColor, boxShadow: `0 0 5px ${dotGlow}` }}
            />
            <span
              className="font-['Cinzel'] text-[7.5px] tracking-wider"
              style={{ color: dotColor }}
            >
              {status}
            </span>
          </div>
        </div>
      </div>

      {/* Corner ornaments — sit on top of the frame */}
      {[
        "top-0 left-0 border-t border-l",
        "top-0 right-0 border-t border-r",
        "bottom-0 left-0 border-b border-l",
        "bottom-0 right-0 border-b border-r",
      ].map((cls, i) => (
        <div
          key={i}
          className={`absolute w-4 h-4 border-[#c8a84b] ${cls}`}
          style={{ borderWidth: "1.5px", opacity: 0.7 }}
        />
      ))}
    </div>
  );
}

// ─── Character Profile Panel ──────────────────────────────────────────────────
interface CharacterPanelProps {
  name: string;
  city: string;
  location: string;
  rank: string;
  role: string;
  status: "Active" | "Away" | "Offline";
  str?: number;
  agi?: number;
  hp?: number;
  mp?: number;
  rep?: number;
  posts?: number;
  badges?: Array<{ label: string; tier: BadgeProps["tier"] }>;
}

function CharacterPanel({
  name,
  city,
  location,
  rank,
  role,
  status,
  str = 42,
  agi = 38,
  hp = 75,
  mp = 55,
  rep = 30,
  posts = 7,
  badges = [],
}: CharacterPanelProps) {
  return (
    <div
      className="relative flex-shrink-0 w-[278px] self-stretch flex flex-col"
      style={{
        background: "linear-gradient(180deg, #161310 0%, #0e0c0a 100%)",
        borderRight: "1px solid rgba(200,168,75,0.18)",
      }}
    >
      {/* ① Portrait */}
      <div className="px-4 pt-5">
        <PortraitFrame status={status} />
      </div>

      {/* ② Name */}
      <div className="px-4 pt-4 text-center">
        <div
          className="font-['Cinzel'] text-[#d4b96e] text-[15px] font-semibold tracking-wide leading-tight"
          style={{ textShadow: "0 0 20px rgba(200,168,75,0.25)" }}
        >
          {name}
        </div>
        <div className="font-['Cinzel'] text-[#6b6050] text-[8.5px] tracking-[0.2em] uppercase mt-1">
          {role}
        </div>
      </div>

      <div className="px-4 mt-3">
        <ThinRule />
      </div>

      {/* ③ Status / ④ City / ⑤ Location / ⑥ Rank */}
      <div className="px-4 mt-3 space-y-2">
        {(
          [
            { icon: MapPin, label: "Kingdom", value: city },
            { icon: MapPin, label: "Location", value: location },
            { icon: Shield, label: "Rank", value: rank },
            { icon: Scroll, label: "Posts", value: `${posts} chronicles` },
          ] as const
        ).map(({ icon: Icon, label, value }) => (
          <div key={label} className="flex items-start gap-2">
            <Icon size={9} className="text-[#c8a84b]/40 mt-[2px] flex-shrink-0" />
            <div className="min-w-0">
              <div className="font-['Cinzel'] text-[7.5px] text-[#6b6050] tracking-[0.15em] uppercase leading-none">
                {label}
              </div>
              <div className="font-['EB_Garamond'] text-[11.5px] text-[#c4b898]/85 leading-tight mt-0.5 break-words">
                {value}
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="px-4 mt-3">
        <ThinRule />
      </div>

      {/* ⑧ Badges */}
      {badges.length > 0 && (
        <>
          <div className="px-4 mt-3">
            <div className="font-['Cinzel'] text-[7.5px] text-[#6b6050] tracking-[0.22em] uppercase mb-2">
              Honours &amp; Medals
            </div>
            <div className="flex flex-wrap gap-1.5">
              {badges.map((b) => (
                <BadgeChip key={b.label} label={b.label} tier={b.tier} />
              ))}
            </div>
          </div>
          <div className="px-4 mt-3">
            <ThinRule />
          </div>
        </>
      )}

      {/* ⑨ Attributes */}
      <div className="px-4 mt-3">
        <div className="font-['Cinzel'] text-[7.5px] text-[#6b6050] tracking-[0.22em] uppercase mb-2.5">
          Attributes
        </div>
        <div className="space-y-2">
          <AttrBar label="STR" value={str} color="#c8a84b" />
          <AttrBar label="AGI" value={agi} color="#7ab0d4" />
          <AttrBar label="HP"  value={hp}  color="#c05050" />
          <AttrBar label="MP"  value={mp}  color="#7060b8" />
          <AttrBar label="REP" value={rep} color="#50a050" />
        </div>
      </div>

      <div className="px-4 mt-3">
        <ThinRule />
      </div>

      {/* ⑩ Faction icons */}
      <div className="px-4 mt-3 pb-5">
        <div className="font-['Cinzel'] text-[7.5px] text-[#6b6050] tracking-[0.22em] uppercase mb-2">
          Faction &amp; Roles
        </div>
        <div className="flex items-center gap-1.5">
          {[
            { Icon: Sword,         title: "Warrior" },
            { Icon: Shield,        title: "Defender" },
            { Icon: Flame,         title: "Pyromancer" },
            { Icon: Star,          title: "Champion" },
            { Icon: Feather,       title: "Scribe" },
          ].map(({ Icon, title }) => (
            <div
              key={title}
              title={title}
              className="w-7 h-7 flex items-center justify-center border border-[#c8a84b]/18 text-[#6b6050] hover:text-[#c8a84b] hover:border-[#c8a84b]/45 hover:bg-[#c8a84b]/6 transition-all cursor-pointer"
            >
              <Icon size={12} />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ─── Post Card ────────────────────────────────────────────────────────────────
interface PostCardProps {
  character: CharacterPanelProps;
  postTime: string;
  content: string;
  postStatus: "Live" | "Pending" | "Approved" | "Locked";
  postNumber: number;
  isAdmin?: boolean;
}

function PostCard({
  character,
  postTime,
  content,
  postStatus,
  postNumber,
  isAdmin = true,
}: PostCardProps) {
  return (
    <article
      className="flex w-full overflow-hidden"
      style={{
        border: "1px solid rgba(200,168,75,0.18)",
        background: "linear-gradient(135deg, #141210 0%, #100f0d 100%)",
        boxShadow: "0 6px 28px rgba(0,0,0,0.45), 0 1px 0 rgba(200,168,75,0.06) inset",
      }}
    >
      {/* LEFT — Character panel */}
      <CharacterPanel {...character} />

      {/* RIGHT — Post content */}
      <div className="flex-1 min-w-0 flex flex-col">
        {/* Post header row */}
        <div
          className="flex items-center justify-between gap-4 px-6 py-3.5 flex-wrap"
          style={{
            borderBottom: "1px solid rgba(200,168,75,0.1)",
            background: "rgba(200,168,75,0.025)",
          }}
        >
          {/* Left: name + rank + location */}
          <div className="flex items-center gap-3 flex-wrap min-w-0">
            <span className="font-['Cinzel'] text-[#c8a84b] text-[13px] font-semibold tracking-wide whitespace-nowrap">
              {character.name}
            </span>
            <div className="w-px h-3 bg-[#c8a84b]/20" />
            <div className="flex items-center gap-1 text-[#6b6050]">
              <Shield size={9} />
              <span className="font-['Cinzel'] text-[8.5px] tracking-wider uppercase whitespace-nowrap">
                {character.rank}
              </span>
            </div>
            <div className="flex items-center gap-1 text-[#6b6050]">
              <MapPin size={9} />
              <span className="font-['Cinzel'] text-[8.5px] tracking-wider whitespace-nowrap">
                {character.location}
              </span>
            </div>
          </div>

          {/* Right: status + time + post# */}
          <div className="flex items-center gap-3 flex-shrink-0">
            <StatusBadge status={postStatus} />
            <div className="flex items-center gap-1 text-[#6b6050]">
              <Clock size={9} />
              <span className="font-['Cinzel'] text-[8.5px] tracking-wider">{postTime}</span>
            </div>
            <span className="font-['Cinzel'] text-[8.5px] text-[#c8a84b]/25 tracking-wider">
              #{postNumber}
            </span>
          </div>
        </div>

        {/* Post body */}
        <div className="flex-1 px-6 py-6">
          <p
            className="font-['EB_Garamond'] text-[#d0c8b0] leading-[1.85] text-[17px]"
            style={{ textShadow: "0 1px 2px rgba(0,0,0,0.4)" }}
          >
            {content}
          </p>
        </div>

        {/* Admin footer */}
        {isAdmin && (
          <div
            className="flex items-center justify-end gap-2 px-6 py-3"
            style={{ borderTop: "1px solid rgba(200,168,75,0.08)" }}
          >
            <span className="font-['Cinzel'] text-[7.5px] text-[#6b6050]/50 tracking-[0.18em] uppercase mr-2">
              Council Actions
            </span>
            {[
              {
                icon: CheckCircle,
                label: "Approve",
                cls: "border-[#4ade80]/22 text-[#4ade80]/65 hover:bg-[#4ade80]/9 hover:border-[#4ade80]/45",
              },
              {
                icon: Edit3,
                label: "Edit",
                cls: "border-[#c8a84b]/22 text-[#c8a84b]/65 hover:bg-[#c8a84b]/9 hover:border-[#c8a84b]/45",
              },
              {
                icon: Trash2,
                label: "Delete",
                cls: "border-[#8b2a2a]/30 text-[#c06060]/60 hover:bg-[#8b2a2a]/12 hover:border-[#8b2a2a]/55",
              },
            ].map(({ icon: Icon, label, cls }) => (
              <button
                key={label}
                className={`flex items-center gap-1.5 px-3 py-1.5 border transition-all font-['Cinzel'] text-[8.5px] tracking-wider uppercase ${cls}`}
              >
                <Icon size={10} />
                {label}
              </button>
            ))}
          </div>
        )}
      </div>
    </article>
  );
}

// ─── Reply Composer ───────────────────────────────────────────────────────────
function ReplyComposer() {
  const [text, setText] = useState("");

  const toolbar = [
    { icon: Bold,      label: "Bold" },
    { icon: Italic,    label: "Italic" },
    { icon: Underline, label: "Underline" },
    null,
    { icon: Link,      label: "Link" },
    { icon: List,      label: "List" },
    { icon: Quote,     label: "Quote" },
  ];

  return (
    <div
      className="w-full overflow-hidden"
      style={{
        border: "1px solid rgba(200,168,75,0.22)",
        background: "linear-gradient(180deg, #141210 0%, #100f0d 100%)",
        boxShadow: "0 6px 32px rgba(0,0,0,0.5), 0 0 0 1px rgba(200,168,75,0.04)",
      }}
    >
      {/* Header */}
      <div
        className="flex items-center justify-between px-6 py-4"
        style={{
          borderBottom: "1px solid rgba(200,168,75,0.12)",
          background: "rgba(200,168,75,0.03)",
        }}
      >
        <div className="flex items-center gap-3">
          <div className="w-7 h-7 border border-[#c8a84b]/35 flex items-center justify-center">
            <FlameKindling size={13} className="text-[#c8a84b]/65" />
          </div>
          <div>
            <div className="font-['Cinzel_Decorative'] text-[#c8a84b] text-[13px] tracking-wider">
              Continue the Tale
            </div>
            <div className="font-['Cinzel'] text-[#6b6050] text-[8px] tracking-wider uppercase mt-0.5">
              Write your reply in character
            </div>
          </div>
        </div>

        {/* Identity chip */}
        <div className="flex items-center gap-2 px-3 py-1.5 border border-[#c8a84b]/18 bg-[#c8a84b]/4">
          <Crown size={10} className="text-[#c8a84b]/55" />
          <span className="font-['Cinzel'] text-[9.5px] text-[#c8a84b]/70 tracking-wider">
            Posting as: <span className="text-[#c8a84b]">Test Player</span>
          </span>
        </div>
      </div>

      {/* Toolbar */}
      <div
        className="flex items-center gap-0.5 px-4 py-2"
        style={{ borderBottom: "1px solid rgba(200,168,75,0.08)" }}
      >
        {toolbar.map((item, i) =>
          item === null ? (
            <div key={i} className="w-px h-3.5 bg-[#c8a84b]/18 mx-1" />
          ) : (
            <button
              key={item.label}
              title={item.label}
              className="p-1.5 text-[#6b6050] hover:text-[#c8a84b] hover:bg-[#c8a84b]/6 transition-all"
            >
              <item.icon size={13} />
            </button>
          )
        )}
      </div>

      {/* Textarea */}
      <div className="px-6 py-5">
        <textarea
          value={text}
          onChange={(e) => setText(e.target.value)}
          placeholder="เขียนโพสต์ของคุณที่นี่…"
          rows={7}
          className="w-full bg-transparent font-['EB_Garamond'] text-[17px] text-[#cfc7af] placeholder-[#6b6050]/55 resize-none focus:outline-none leading-[1.85]"
          style={{ caretColor: "#c8a84b" }}
        />
      </div>

      {/* Footer */}
      <div
        className="flex items-center justify-between px-6 py-4"
        style={{ borderTop: "1px solid rgba(200,168,75,0.1)" }}
      >
        <div className="flex items-center gap-2 text-[#6b6050]">
          <Zap size={11} className="text-[#f59e0b]/45 flex-shrink-0" />
          <span className="font-['Cinzel'] text-[8.5px] tracking-wider">
            Your reply will appear after Council approval
          </span>
        </div>
        <div className="flex items-center gap-4">
          <span className="font-['Cinzel'] text-[8.5px] text-[#6b6050]/45 tracking-wider">
            {text.length} chars
          </span>
          <button
            className="flex items-center gap-2 px-6 py-2.5 font-['Cinzel'] text-[10.5px] tracking-[0.15em] uppercase transition-all"
            style={{
              background: "linear-gradient(135deg, #c8a84b 0%, #8b6914 100%)",
              color: "#0c0b09",
              fontWeight: 600,
              boxShadow: "0 0 18px rgba(200,168,75,0.22), 0 2px 8px rgba(0,0,0,0.4)",
            }}
            onMouseEnter={(e) => {
              (e.currentTarget as HTMLButtonElement).style.boxShadow =
                "0 0 28px rgba(200,168,75,0.38), 0 2px 8px rgba(0,0,0,0.4)";
            }}
            onMouseLeave={(e) => {
              (e.currentTarget as HTMLButtonElement).style.boxShadow =
                "0 0 18px rgba(200,168,75,0.22), 0 2px 8px rgba(0,0,0,0.4)";
            }}
          >
            <Scroll size={12} />
            Post Reply
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── Footer ───────────────────────────────────────────────────────────────────
function Footer() {
  const worlds   = ["Aurantia", "Kalfir", "Viente", "Akancia", "Kingsbridge"];
  const explore  = [
    { label: "World Map",           icon: Map },
    { label: "Villages & Forums",   icon: Home },
    { label: "Join the Chronicle",  icon: BookOpen },
  ];

  return (
    <footer
      className="w-full mt-20"
      style={{
        borderTop: "1px solid rgba(200,168,75,0.12)",
        background: "linear-gradient(180deg, #0a0908 0%, #070605 100%)",
      }}
    >
      <div className="mx-auto max-w-[1180px] px-8 py-14">
        <div className="grid grid-cols-3 gap-12 mb-10">
          <div>
            <VaelthornLogo />
            <p className="font-['EB_Garamond'] text-[#6b6050] text-[14px] mt-4 leading-relaxed">
              A living world of dark fantasy, shared storytelling, and noble chronicles.
              Enter the realm. Write your legend.
            </p>
          </div>
          <div>
            <div className="font-['Cinzel'] text-[#c8a84b]/55 text-[8.5px] tracking-[0.3em] uppercase mb-4">
              Explore
            </div>
            <div className="space-y-3">
              {explore.map(({ label, icon: Icon }) => (
                <div key={label} className="flex items-center gap-2.5 group cursor-pointer">
                  <Icon size={10} className="text-[#6b6050] group-hover:text-[#c8a84b] transition-colors" />
                  <span className="font-['Cinzel'] text-[10.5px] text-[#6b6050] group-hover:text-[#c8a84b] transition-colors tracking-wider">
                    {label}
                  </span>
                </div>
              ))}
            </div>
          </div>
          <div>
            <div className="font-['Cinzel'] text-[#c8a84b]/55 text-[8.5px] tracking-[0.3em] uppercase mb-4">
              The World
            </div>
            <div className="space-y-3">
              {worlds.map((w) => (
                <div key={w} className="flex items-center gap-2.5 group cursor-pointer">
                  <ChevronRight size={9} className="text-[#6b6050]/45 group-hover:text-[#c8a84b] transition-colors" />
                  <span className="font-['Cinzel'] text-[10.5px] text-[#6b6050] group-hover:text-[#c8a84b] transition-colors tracking-wider">
                    {w}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        <GoldDivider />

        <div className="flex items-center justify-between mt-6">
          <span className="font-['Cinzel'] text-[8px] text-[#6b6050]/45 tracking-[0.22em]">
            © 2024 VAELTHORN CHRONICLES — ALL RIGHTS RESERVED
          </span>
          <span className="font-['EB_Garamond'] text-[12px] text-[#6b6050]/38 italic">
            "Where legends are written in shadow and gold."
          </span>
        </div>
      </div>
    </footer>
  );
}

// ─── Main App ─────────────────────────────────────────────────────────────────
export default function App() {
  const testPlayer: CharacterPanelProps = {
    name: "Test Player",
    city: "Aurantia — The Golden Dominion",
    location: "Aurantia",
    rank: "Commoner",
    role: "Wandering Scribe",
    status: "Active",
    str: 42,
    agi: 55,
    hp: 68,
    mp: 72,
    rep: 30,
    posts: 7,
    badges: [
      { label: "New Blood", tier: "silver" },
      { label: "Scribe",    tier: "gold"   },
    ],
  };

  const councilMember: CharacterPanelProps = {
    name: "Lyraen Voss",
    city: "Viente — The Silver Coast",
    location: "Viente Harbor",
    rank: "Council Elder",
    role: "Chronicler of Viente",
    status: "Active",
    str: 58,
    agi: 61,
    hp: 85,
    mp: 90,
    rep: 88,
    posts: 142,
    badges: [
      { label: "Elder",       tier: "gold"    },
      { label: "Chronicler",  tier: "emerald" },
      { label: "Arbiter",     tier: "crimson" },
    ],
  };

  return (
    <div
      className="min-h-screen bg-background text-foreground"
      style={{ fontFamily: "'EB Garamond', Georgia, serif" }}
    >
      <TopNav />

      {/* Subtle grid background */}
      <div
        className="fixed inset-0 pointer-events-none opacity-[0.018] z-0"
        style={{
          backgroundImage: [
            "repeating-linear-gradient(0deg, transparent, transparent 44px, rgba(200,168,75,1) 44px, rgba(200,168,75,1) 45px)",
            "repeating-linear-gradient(90deg, transparent, transparent 44px, rgba(200,168,75,1) 44px, rgba(200,168,75,1) 45px)",
          ].join(", "),
        }}
      />

      <main className="relative z-10 mx-auto max-w-[1180px] px-6 py-8">
        {/* Back link */}
        <button className="flex items-center gap-2 mb-6 group">
          <ArrowLeft
            size={12}
            className="text-[#6b6050] group-hover:text-[#c8a84b] transition-colors"
          />
          <span className="font-['Cinzel'] text-[9.5px] tracking-[0.2em] uppercase text-[#6b6050] group-hover:text-[#c8a84b] transition-colors">
            Back to Viente
          </span>
        </button>

        {/* Thread header */}
        <div
          className="mb-6 px-7 py-6 relative overflow-hidden"
          style={{
            border: "1px solid rgba(200,168,75,0.2)",
            background: "linear-gradient(135deg, #141210 0%, #0e0d0b 100%)",
            boxShadow: "0 4px 24px rgba(0,0,0,0.4)",
          }}
        >
          {/* Corner ornaments */}
          {["top-0 left-0 border-t border-l", "top-0 right-0 border-t border-r",
            "bottom-0 left-0 border-b border-l", "bottom-0 right-0 border-b border-r"].map((cls, i) => (
            <div
              key={i}
              className={`absolute w-4 h-4 border-[#c8a84b]/45 ${cls}`}
              style={{ borderWidth: "1.2px" }}
            />
          ))}

          <div className="flex items-start justify-between gap-6">
            <div className="flex-1">
              <h1
                className="font-['Cinzel_Decorative'] text-[#c8a84b] text-[22px] font-bold leading-tight mb-3"
                style={{ textShadow: "0 0 28px rgba(200,168,75,0.28)" }}
              >
                หกดหดหกดหกดหด
              </h1>
              <div className="flex items-center gap-4 flex-wrap">
                <StatusBadge status="Live" />
                <div className="flex items-center gap-1.5 text-[#6b6050]">
                  <MapPin size={10} />
                  <span className="font-['Cinzel'] text-[9px] tracking-wider">Viente</span>
                </div>
                <div className="flex items-center gap-1.5 text-[#6b6050]">
                  <Scroll size={10} />
                  <span className="font-['Cinzel'] text-[9px] tracking-wider">2 posts</span>
                </div>
              </div>
            </div>
          </div>

          {/* Approval notice */}
          <div
            className="mt-5 flex items-center gap-3 px-4 py-3"
            style={{
              border: "1px solid rgba(245,158,11,0.22)",
              background: "rgba(245,158,11,0.05)",
            }}
          >
            <Zap size={12} className="text-[#f59e0b]/70 flex-shrink-0" />
            <div>
              <span className="font-['Cinzel'] text-[8.5px] tracking-wider text-[#f59e0b]/80 uppercase">
                Council Notice
              </span>
              <p className="font-['EB_Garamond'] text-[#c4b898]/75 text-[14px] mt-0.5">
                This post is waiting for Council approval
              </p>
            </div>
          </div>
        </div>

        {/* Posts */}
        <div className="space-y-0">
          <PostCard
            character={testPlayer}
            postTime="2 hours ago"
            postStatus="Pending"
            postNumber={1}
            content="หดหดหดหกดหดหดหดหดหดหด — อักษรแห่งชะตากรรมที่บันทึกไว้ในหน้ากระดาษของโลก Vaelthorn ดินแดนแห่งเงามืดและทองคำ ที่ซึ่งวีรบุรุษและอาชญากรเดินเคียงกัน และชะตาของทั้งโลกถูกเขียนขึ้นใหม่ทุกเมื่อ"
            isAdmin={true}
          />

          <div className="flex items-center gap-4 py-5 px-2">
            <GoldDivider />
          </div>

          <PostCard
            character={councilMember}
            postTime="1 hour ago"
            postStatus="Approved"
            postNumber={2}
            content="ข้าได้อ่านบันทึกของเจ้าแล้ว นักเดินทางผู้หลงทาง คำพูดของเจ้ามีน้ำหนักดุจทองแห่ง Aurantia แต่หนทางสู่ความจริงนั้นยังไกล จงเตรียมใจให้พร้อม เพราะโลก Vaelthorn จะทดสอบเจ้าอีกครั้ง"
            isAdmin={true}
          />
        </div>

        {/* Reply composer */}
        <div className="mt-8">
          <ReplyComposer />
        </div>
      </main>

      <Footer />
    </div>
  );
}
