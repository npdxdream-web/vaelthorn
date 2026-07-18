import { Link } from "react-router";
import { Compass } from "lucide-react";
import { Button } from "../components/ui/button";

export function NotFound() {
  return (
    <div className="vaelthorn-page flex min-h-[80vh] items-center justify-center px-6">
      <div className="text-center">
        <Compass className="mx-auto mb-6 h-24 w-24 text-[#686664]" />
        <h1 className="font-display mb-3 text-4xl tracking-wide text-[#D4AF37]">
          Lost in the Mists
        </h1>
        <p className="mb-8 text-lg text-[#a8a6a3]">
          This path does not exist in the world of Thiran.
        </p>
        <Link to="/">
          <Button className="bg-[#D4AF37] text-[#0f0f0f] hover:bg-[#B8941F]">
            Return to the World Map
          </Button>
        </Link>
      </div>
    </div>
  );
}
