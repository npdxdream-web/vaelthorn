import { Flame, Map, BookOpen } from "lucide-react";
import { Link } from "react-router";

export function Footer() {
  return (
    <footer className="relative z-10 mt-24 border-t border-[#c8a84b]/15 bg-[linear-gradient(180deg,#0a0908_0%,#070605_100%)]">
      {/* Ornamental divider */}
      <div className="flex items-center justify-center py-6">
        <div className="h-px w-24 bg-gradient-to-r from-transparent to-[#c8a84b]/45" />
        <Flame className="mx-4 h-4 w-4 text-[#c8a84b]/60" />
        <div className="h-px w-24 bg-gradient-to-l from-transparent to-[#c8a84b]/45" />
      </div>

      <div className="mx-auto max-w-6xl px-6 pb-12">
        <div className="grid grid-cols-1 gap-10 md:grid-cols-3">
          {/* Brand */}
          <div className="space-y-4">
            <h2 className="font-decorative text-2xl tracking-widest text-[#c8a84b]">
              Vaelthorn
            </h2>
            <p className="font-chronicle text-base leading-relaxed text-[#746a5a]">
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
            <h3 className="archive-label">
              Explore
            </h3>
            <nav className="flex flex-col gap-2">
              <Link
                to="/"
                className="flex items-center gap-2 text-sm text-[#746a5a] transition-colors hover:text-[#c8a84b]"
              >
                <Map className="h-3.5 w-3.5" />
                World Map
              </Link>
              {/* /chronicles is a Blade page, not part of this SPA yet — full navigation */}
              <a
                href="/chronicles"
                className="flex items-center gap-2 text-sm text-[#746a5a] transition-colors hover:text-[#c8a84b]"
              >
                <BookOpen className="h-3.5 w-3.5" />
                Chronicles
              </a>
            </nav>
          </div>

          {/* World lore snippet */}
          <div className="space-y-4">
            <h3 className="archive-label">
              The World of Thiran
            </h3>
            <ul className="space-y-2 text-sm text-[#746a5a]">
              {[
                "The Ashfield Wastes",
                "Ironcliff Holds",
                "Mirewood Hollows",
                "The Sunken Archive",
                "Cradle of the First Flame",
              ].map((place) => (
                <li
                  key={place}
                  className="flex items-center gap-2 before:block before:h-px before:w-3 before:bg-[#c8a84b]/35"
                >
                  {place}
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="mt-12 flex flex-col items-center gap-2 border-t border-[#c8a84b]/10 pt-6 text-center md:flex-row md:justify-between">
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
