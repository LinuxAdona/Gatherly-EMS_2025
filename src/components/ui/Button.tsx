interface Props {
  children?: React.ReactNode;
  auth?: "google" | "facebook";
  className?: string;
}

const Button = ({ children, auth, className }: Props) => {
  return (
    <div className={className}>
      <button
        className={`w-full cursor-pointer py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 ${
          !!auth
            ? "bg-white border shadow-sm border-gray-300 text-black rounded-md hover:bg-gray-200"
            : "btn-primary bg-blue-600 text-white rounded hover:bg-blue-700"
        }`}
      >
        <div
          className={`flex items-center justify-center ${!!auth && "text-sm"}`}
        >
          {(() => {
            switch (auth) {
              case "google":
                return (
                  <img
                    className="w-5 h-5 mr-2"
                    src="/Logos/google.svg"
                    alt="Google Logo"
                  />
                );
              case "facebook":
                return (
                  <img
                    className="w-5 h-5 mr-2"
                    src="/Logos/facebook.svg"
                    alt="Facebook Logo"
                  />
                );
              default:
                return null;
            }
          })()}
          {children}
        </div>
      </button>
    </div>
  );
};

export default Button;
