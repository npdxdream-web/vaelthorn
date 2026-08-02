import { createBrowserRouter } from "react-router";
import { Layout } from "./components/Layout";
import { HomePage } from "./pages/HomePage";
import { VillagePage } from "./pages/VillagePage";
import { ThreadPage } from "./pages/ThreadPage";
import { RegisterPage } from "./pages/RegisterPage";
import { CharacterPage } from "./pages/CharacterPage";
import { NotFound } from "./pages/NotFound";

export const router = createBrowserRouter([
  {
    path: "/",
    Component: Layout,
    children: [
      { index: true, Component: HomePage },
      { path: "village/:villageId", Component: VillagePage },
      { path: "thread/:threadId", Component: ThreadPage },
      { path: "register", Component: RegisterPage },
      { path: "character/:characterId", Component: CharacterPage },
      { path: "*", Component: NotFound },
    ],
  },
]);
