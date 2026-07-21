import { createBrowserRouter } from "react-router";
import { Layout } from "./components/Layout";
import { HomePage } from "./pages/HomePage";
import { CityPage } from "./pages/CityPage";
import { ThreadPage } from "./pages/ThreadPage";
import { RegisterPage } from "./pages/RegisterPage";
import { CharacterPage } from "./pages/CharacterPage";
import { RecentActivityPage } from "./pages/RecentActivityPage";
import { NotFound } from "./pages/NotFound";

export const router = createBrowserRouter(
  [
    {
      path: "/",
      Component: Layout,
      children: [
        { index: true, Component: HomePage },
        { path: "city/:cityId", Component: CityPage },
        { path: "thread/:threadId", Component: ThreadPage },
        { path: "register", Component: RegisterPage },
        { path: "character/:characterId", Component: CharacterPage },
        { path: "activity", Component: RecentActivityPage },
        { path: "*", Component: NotFound },
      ],
    },
  ],
  {
    basename: "/app",
  }
);
