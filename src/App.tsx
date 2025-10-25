import Button from "./components/ui/Button";
import { Link } from "react-router-dom";

function App() {
  return (
    <>
      <div className="flex flex-col h-screen bg-gray-100">
        <nav className="h-16 bg-white/85 flex items-center px-4 w-full">
          <div className="flex items-center justify-between w-full">
            <div className="flex flex-row items-center">
              <img
                className="w-14 h-14"
                src="/Logos/GEMS-LOGO-ONLY-1.png"
                alt="Logo"
              />
              <img
                className="w-20"
                src="/Logos/GEMS-TEXT-LOGO-ONLY.png"
                alt="Text Logo"
              />
            </div>
            <div className="flex flex-row items-center">
              <div className="mx-4 flex flex-row items-center gap-4 font-medium">
                <Link className=" hover:text-blue-600" to="/">
                  Home
                </Link>
                <Link className=" hover:text-blue-600" to="/about">
                  About
                </Link>
              </div>
              <Link to="/login">
                <Button>Sign in</Button>
              </Link>
            </div>
          </div>
        </nav>
      </div>
    </>
  );
}

export default App;
