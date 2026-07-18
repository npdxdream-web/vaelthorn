import { Link } from "react-router";
import { MapPin, ArrowRight } from "lucide-react";
import { CharacterModule } from "../components/CharacterModule";
import { cities, threads } from "../data/mockData";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";
import { Button } from "../components/ui/button";

export function HomePage() {
  return (
    <div className="vaelthorn-page mx-auto max-w-7xl px-6 py-8">
      <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {/* Left: World Map Area */}
        <div className="lg:col-span-2">
          <div className="mb-6">
            <h1 className="font-display mb-2 text-3xl tracking-wide text-[#D4AF37]">
              The World of Thiran
            </h1>
            <p className="text-[#a8a6a3]">
              Choose your path across four legendary cities, each with their own tales to tell.
            </p>
          </div>

          {/* Map Visual */}
          <div className="glow-gold relative mb-8 overflow-hidden rounded-xl border border-[#2a2a2a] bg-[#1a1a1a]">
            <div className="relative h-96">
              <ImageWithFallback 
                src="https://images.unsplash.com/photo-1520299607509-dcd935f9a839?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxmYW50YXN5JTIwbWVkaWV2YWwlMjBtYXAlMjBwYXJjaG1lbnR8ZW58MXx8fHwxNzgxNDkxNzI3fDA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
                alt="Map of Thiran"
                className="h-full w-full object-cover opacity-30"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-[#0f0f0f] via-transparent to-transparent" />
              
              {/* City Markers */}
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="grid grid-cols-2 gap-16">
                  {cities.map((city, idx) => (
                    <Link 
                      key={city.id}
                      to={`/village/${city.villages[0].id}`}
                      className="group relative flex flex-col items-center"
                    >
                      <div 
                        className="mb-2 flex h-16 w-16 items-center justify-center rounded-full border-2 bg-[#1a1a1a]/90 text-2xl backdrop-blur-sm transition-all group-hover:scale-110 group-hover:bg-[#2a2a2a]"
                        style={{ borderColor: city.color }}
                      >
                        {city.icon}
                      </div>
                      <span 
                        className="font-display text-sm transition-colors"
                        style={{ color: city.color }}
                      >
                        {city.name}
                      </span>
                    </Link>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* City Cards */}
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {cities.map((city) => (
              <div 
                key={city.id}
                className="group overflow-hidden rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] transition-all hover:border-[#D4AF37]"
              >
                <div 
                  className="border-b px-4 py-3"
                  style={{ borderColor: city.color + '40', backgroundColor: city.color + '10' }}
                >
                  <div className="flex items-center gap-3">
                    <span className="text-2xl">{city.icon}</span>
                    <h3 className="font-display text-lg" style={{ color: city.color }}>
                      {city.name}
                    </h3>
                  </div>
                </div>
                <div className="p-4">
                  <p className="mb-4 text-sm text-[#a8a6a3]">{city.description}</p>
                  <div className="space-y-2">
                    {city.villages.map((village) => (
                      <Link 
                        key={village.id}
                        to={`/village/${village.id}`}
                        className="flex items-center justify-between rounded-lg border border-transparent px-3 py-2 text-sm transition-all hover:border-[#2a2a2a] hover:bg-[#141414]"
                      >
                        <div className="flex items-center gap-2">
                          <MapPin className="h-4 w-4 text-[#a8a6a3]" />
                          <span className="text-[#e8e6e3]">{village.name}</span>
                        </div>
                        <ArrowRight className="h-4 w-4 text-[#686664]" />
                      </Link>
                    ))}
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Recent Activity */}
          <div className="mt-8">
            <h2 className="font-display mb-4 text-xl text-[#D4AF37]">Recent Tales</h2>
            <div className="space-y-3">
              {threads.slice(0, 3).map((thread) => (
                <Link 
                  key={thread.id}
                  to={`/thread/${thread.id}`}
                  className="group block rounded-xl border border-[#2a2a2a] bg-[#1a1a1a] p-4 transition-all hover:border-[#D4AF37]"
                >
                  <div className="mb-2 flex items-start justify-between">
                    <h3 className="font-medium text-[#e8e6e3] group-hover:text-[#D4AF37]">
                      {thread.title}
                    </h3>
                    <span className="text-xs text-[#686664]">{thread.lastPost}</span>
                  </div>
                  <div className="flex items-center gap-4 text-xs text-[#a8a6a3]">
                    <span>{thread.author.name}</span>
                    <span>•</span>
                    <span>{thread.village}</span>
                    <span>•</span>
                    <span>{thread.replies} replies</span>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </div>

        {/* Right: Character Module */}
        <div className="lg:col-span-1">
          <CharacterModule />
        </div>
      </div>
    </div>
  );
}
