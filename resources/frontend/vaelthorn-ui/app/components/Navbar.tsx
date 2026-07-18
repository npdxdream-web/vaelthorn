import { Link, useLocation } from "react-router";
import { Crown, Home, Map, Users, Scroll, User } from "lucide-react";
import { Button } from "./ui/button";

export function Navbar() {
  const location = useLocation();
  
  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <nav className="sticky top-0 z-50 border-b border-[#c8a84b]/15 bg-[#090807]/95 backdrop-blur-md">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
        {/* Logo */}
        <Link to="/" className="group flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center border border-[#c8a84b]/45 bg-[#161310] shadow-[0_0_18px_rgba(200,168,75,0.12)] transition-colors group-hover:border-[#c8a84b]/75">
            <Scroll className="h-5 w-5 text-[#c8a84b]" />
          </div>
          <div>
            <span className="font-decorative block text-sm font-bold leading-none tracking-[0.22em] text-[#c8a84b]">
              Vaelthorn
            </span>
            <span className="font-display mt-1 block text-[0.5rem] uppercase tracking-[0.36em] text-[#746a5a]">
              Chronicles
            </span>
          </div>
        </Link>

        {/* Center Navigation */}
        <div className="flex items-center gap-1">
          <Link to="/">
            <Button 
              variant="ghost" 
              size="icon"
              className={`h-10 w-10 rounded-none border border-transparent ${isActive('/') ? 'border-[#c8a84b]/25 bg-[#c8a84b]/8 text-[#c8a84b]' : 'text-[#746a5a] hover:bg-[#c8a84b]/6 hover:text-[#c8a84b]'}`}
            >
              <Home className="h-5 w-5" />
            </Button>
          </Link>
          <Link to="/village/ironveil">
            <Button 
              variant="ghost" 
              size="icon"
              className={`h-10 w-10 rounded-none border border-transparent ${location.pathname.includes('/village') ? 'border-[#c8a84b]/25 bg-[#c8a84b]/8 text-[#c8a84b]' : 'text-[#746a5a] hover:bg-[#c8a84b]/6 hover:text-[#c8a84b]'}`}
            >
              <Map className="h-5 w-5" />
            </Button>
          </Link>
          <Link to="/register">
            <Button 
              variant="ghost" 
              size="icon"
              className={`h-10 w-10 rounded-none border border-transparent ${isActive('/register') ? 'border-[#c8a84b]/25 bg-[#c8a84b]/8 text-[#c8a84b]' : 'text-[#746a5a] hover:bg-[#c8a84b]/6 hover:text-[#c8a84b]'}`}
            >
              <Users className="h-5 w-5" />
            </Button>
          </Link>
        </div>

        {/* Right Side - User */}
        <div className="flex items-center gap-2">
          <Link to="/character/aelric">
            <Button 
              variant="ghost" 
              size="icon"
              className="h-10 w-10 overflow-hidden rounded-full border-2 border-[#c8a84b]/35 bg-[#1a1713] p-0 hover:border-[#c8a84b]/70"
            >
              <div className="relative flex h-full w-full items-center justify-center bg-[#141210]">
                <Crown className="h-4 w-4 text-[#c8a84b]/70" />
                <User className="sr-only" />
              </div>
            </Button>
          </Link>
        </div>
      </div>
    </nav>
  );
}
