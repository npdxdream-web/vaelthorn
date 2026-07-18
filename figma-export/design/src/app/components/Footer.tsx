import { Flame, Scroll, Map, Users, BookOpen } from "lucide-react";
import { Link } from "react-router";

export function Footer() {
  return (
    <footer className="mt-24 border-t border-[#2a2520] bg-[#0a0a0a]">
      {/* Ornamental divider */}
      <div className="flex items-center justify-center py-6">
        <div className="h-px w-24 bg-gradient-to-r from-transparent to-[#D4AF37]/40" />
        <Flame className="mx-4 h-4 w-4 text-[#D4AF37]/60" />
        <div className="h-px w-24 bg-gradient-to-l from-transparent to-[#D4AF37]/40" />
      </div>

      <div className="mx-auto max-w-6xl px-6 pb-12">
        <div className="grid grid-cols-1 gap-10 md:grid-cols-3">
          {/* Brand */}
          <div className="space-y-4">
            <h2 className="font-display text-2xl tracking-widest text-[#D4AF37]">
              Vaelthorn
            </h2>
            <p className="text-sm leading-relaxed text-[#686664]">
              A living chronicle of the world of Thiran — where every story
              leaves its mark upon the age.
            </p>
            <p className="text-xs text-[#4a4846] italic">
              "The age does not forget those who dare to write themselves into
              it."
            </p>
          </div>

          {/* Navigation */}
          <div className="space-y-4">
            <h3 className="text-xs font-semibold uppercase tracking-widest text-[#a8a6a3]">
              Explore
            </h3>
            <nav className="flex flex-col gap-2">
              {[
                { to: "/", icon: Map, label: "World Map" },
                { to: "/village/iron-crossing", icon: Users, label: "Villages & Forums" },
                { to: "/thread/1", icon: BookOpen, label: "Chronicles" },
                { to: "/character/1", icon: Scroll, label: "Characters" },
              ].map(({ to, icon: Icon, label }) => (
                <Link
                  key={to}
                  to={to}
                  className="flex items-center gap-2 text-sm text-[#686664] transition-colors hover:text-[#D4AF37]"
                >
                  <Icon className="h-3.5 w-3.5" />
                  {label}
                </Link>
              ))}
            </nav>
          </div>

          {/* World lore snippet */}
          <div className="space-y-4">
            <h3 className="text-xs font-semibold uppercase tracking-widest text-[#a8a6a3]">
              The World of Thiran
            </h3>
            <ul className="space-y-2 text-sm text-[#686664]">
              {[
                "The Ashfield Wastes",
                "Ironcliff Holds",
                "Mirewood Hollows",
                "The Sunken Archive",
                "Cradle of the First Flame",
              ].map((place) => (
                <li
                  key={place}
                  className="flex items-center gap-2 before:block before:h-px before:w-3 before:bg-[#D4AF37]/30"
                >
                  {place}
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="mt-12 flex flex-col items-center gap-2 border-t border-[#1e1c1a] pt-6 text-center md:flex-row md:justify-between">
          <p className="text-xs text-[#3a3836]">
            &copy; {new Date().getFullYear()} Vaelthorn. All chronicles
            reserved.
          </p>
          <div className="flex gap-4 text-xs text-[#3a3836]">
            <span className="cursor-default hover:text-[#686664] transition-colors">Lore Guidelines</span>
            <span className="cursor-default hover:text-[#686664] transition-colors">Community Accord</span>
            <span className="cursor-default hover:text-[#686664] transition-colors">Contact the Council</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
