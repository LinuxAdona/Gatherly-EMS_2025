import InputForm from "./ui/InputForm";
import Button from "./ui/Button";

const Login = () => {
  return (
    <div className="flex flex-col items-end justify-center min-h-screen bg-gray-100">
      <div className="absolute inset-0 z-0">
        <img
          className="object-cover w-full h-full"
          src="/Photos/Login-BG.jpg"
          alt="Background"
        />
      </div>
      <div className="z-10 flex items-center justify-center w-2/5 h-screen p-16 border border-gray-400 bg-white/85 bg-opacity-80 backdrop-blur-sm">
        <div className="w-full">
          <div className="flex flex-col items-center justify-center">
            <img
              className="w-32 h-32"
              src="/Logos/GEMS-LOGO-ONLY-1.png"
              alt="logo"
            />
            <div className="flex items-center">
              <h2 className="text-2xl font-bold text-cyan-900">Sign in to</h2>
              <img
                className="w-24"
                src="/Logos/GEMS-TEXT-LOGO-ONLY.png"
                alt="text-logo"
              />
            </div>
          </div>
          <div className="flex flex-col w-full gap-1 mt-8">
            <InputForm label="Username and E-mail" />
            <InputForm label="Password" type="password" />
          </div>
          <Button className="mt-4">Sign in</Button>
          <div className="flex items-center my-4">
            <hr className="border-t border-gray-400 grow" />
            <span className="mx-2 text-gray-500">or</span>
            <hr className="border-t border-gray-400 grow" />
          </div>
          <div className="flex flex-col gap-2">
            <Button auth="google">Continue with Google</Button>
            <Button auth="facebook">Continue with Facebook</Button>
          </div>
          <div className="mt-2 text-center">
            <span className="text-sm text-gray-500">
              Don't have an account?{" "}
            </span>
            <a href="#" className="text-sm text-blue-800 hover:underline">
              Create an account
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;
