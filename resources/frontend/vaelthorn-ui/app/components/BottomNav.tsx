import { Link, useLocation } from "react-router";
import {
  Home,
  Clock,
  Calendar,
  BookOpen,
  ShoppingCart,
  Gift,
  Bell,
} from "lucide-react";

interface BottomNavProps {
  notificationCount?: number;
}

// `external: true` items are real pages on the Blade app (not part of this SPA yet),
// so they use a plain <a> for a full page navigation instead of client-side <Link>.
const NAV_ITEMS = [
  { icon: Home, to: "/", label: "Home", external: false },
  { icon: Clock, to: "/activity", label: "Activity", external: false },
  { icon: Calendar, to: "/events", label: "Events", external: true },
  { icon: BookOpen, to: "/chronicles", label: "Chronicles", external: true },
  { icon: ShoppingCart, to: "/market", label: "Market", external: true },
  { icon: Gift, to: "/rewards", label: "Rewards", external: true },
];

export function BottomNav({ notificationCount = 0 }: BottomNavProps) {
  const location = useLocation();

  const isActive = (path: string) => {
    if (path === "/") return location.pathname === "/";
    return location.pathname.startsWith(path);
  };

  const itemClass = (active: boolean) =>
    `flex flex-col items-center gap-0.5 rounded p-1.5 transition-colors ${
      active ? "text-[#c8a84b]" : "text-[#746a5a] hover:text-[#c8a84b]/70"
    }`;

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 border-t border-[#c8a84b]/15 bg-[#090807]/95 backdrop-blur-md">
      <div className="mx-auto flex max-w-lg items-center justify-around px-1 py-3">
        {NAV_ITEMS.map(({ icon: Icon, to, label, external }) =>
          external ? (
            <a key={to} href={to} aria-label={label} className={itemClass(false)}>
              <Icon className="h-5 w-5" />
            </a>
          ) : (
            <Link key={to} to={to} aria-label={label} className={itemClass(isActive(to))}>
              <Icon className="h-5 w-5" />
            </Link>
          )
        )}

        {/* Bell with notification badge — /notifications is a Blade page, full navigation */}
        <a
          href="/notifications"
          aria-label="Notifications"
          className="relative flex flex-col items-center gap-0.5 rounded p-1.5 text-[#746a5a] transition-colors hover:text-[#c8a84b]/70"
        >
          <Bell className="h-5 w-5" />
          {notificationCount > 0 && (
            <span className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-0.5 text-[9px] font-bold leading-none text-white">
              {notificationCount > 99 ? "99+" : notificationCount}
            </span>
          )}
        </a>
      </div>
    </nav>
  );
}
