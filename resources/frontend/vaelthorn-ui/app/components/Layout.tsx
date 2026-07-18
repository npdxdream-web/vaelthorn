import { Outlet } from "react-router";
import { Navbar } from "./Navbar";
import { Footer } from "./Footer";
import { BottomNav } from "./BottomNav";
import { characters } from "../data/mockData";

export function Layout() {
  const notificationCount = characters.aelric.notificationCount;

  return (
    <div className="relative min-h-screen overflow-hidden bg-[#090807] text-[#efe7d2]">
      <div
        className="pointer-events-none fixed inset-0 z-0 opacity-[0.022]"
        style={{
          backgroundImage: [
            "repeating-linear-gradient(0deg, transparent, transparent 44px, rgba(200,168,75,1) 44px, rgba(200,168,75,1) 45px)",
            "repeating-linear-gradient(90deg, transparent, transparent 44px, rgba(200,168,75,1) 44px, rgba(200,168,75,1) 45px)",
          ].join(", "),
        }}
      />
      <div className="pointer-events-none fixed inset-x-0 top-0 z-0 h-96 bg-[radial-gradient(circle_at_50%_-20%,rgba(200,168,75,0.12),transparent_62%)]" />
      <Navbar />
      <main className="relative z-10 flex-1">
        <Outlet />
      </main>
      <Footer />
      <BottomNav notificationCount={notificationCount} />
    </div>
  );
}
