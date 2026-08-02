import { Link, useLocation } from "react-router";
import { Home, Map, Users, Scroll, User } from "lucide-react";
import { Button } from "./ui/button";

export function Navbar() {
  const location = useLocation();
  
  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <nav className="sticky top-0 z-50 border-b border-[#2a2a2a] bg-[#141414]/95 backdrop-blur-sm">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
        {/* Logo */}
        <Link to="/" className="flex items-center gap-2">
          <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-[#D4AF37] to-[#B8941F]">
            <Scroll className="h-5 w-5 text-[#0f0f0f]" />
          </div>
          <span className="font-display text-xl tracking-wide text-[#D4AF37]">
            Vaelthorn
          </span>
        </Link>

        {/* Center Navigation */}
        <div className="flex items-center gap-1">
          <Link to="/">
            <Button 
              variant="ghost" 
              size="icon"
              className={`h-10 w-10 ${isActive('/') ? 'bg-[#2a2a2a] text-[#D4AF37]' : 'text-[#a8a6a3] hover:text-[#e8e6e3]'}`}
            >
              <Home className="h-5 w-5" />
            </Button>
          </Link>
          <Link to="/village/ironveil">
            <Button 
              variant="ghost" 
              size="icon"
              className={`h-10 w-10 ${location.pathname.includes('/village') ? 'bg-[#2a2a2a] text-[#D4AF37]' : 'text-[#a8a6a3] hover:text-[#e8e6e3]'}`}
            >
              <Map className="h-5 w-5" />
            </Button>
          </Link>
          <Link to="/register">
            <Button 
              variant="ghost" 
              size="icon"
              className={`h-10 w-10 ${isActive('/register') ? 'bg-[#2a2a2a] text-[#D4AF37]' : 'text-[#a8a6a3] hover:text-[#e8e6e3]'}`}
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
              className="h-10 w-10 overflow-hidden rounded-full border-2 border-[#7a8c9e] p-0 hover:border-[#D4AF37]"
            >
              <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#7a8c9e] to-[#5a6c7e]">
                <User className="h-5 w-5 text-[#e8e6e3]" />
              </div>
            </Button>
          </Link>
        </div>
      </div>
    </nav>
  );
}
