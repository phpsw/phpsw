server "52.32.33.166",
  user: "ubuntu",
  roles: %w[app web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }
