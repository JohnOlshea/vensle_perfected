import axios from "axios";

const API_URL = "http://localhost:8000/api/v1/";

const register = (name, email, password, password_confirmation) => {
  return axios.post(API_URL + "register", {
    name,
    email,
    password,
    password_confirmation,
  });
};

const login = (email, password) => {
  return axios
    .post(API_URL + "login", {
      email,
      password,
    })
    .then((response) => {
      // console.log(response);
      if (response.data.token) {
        localStorage.setItem("user", JSON.stringify(response.data));
      }

      


      return response.data;
    });
};

const logout = () => {
  localStorage.removeItem("user");
};

export default {
  register,
  login,
  logout,
};