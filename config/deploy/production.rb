server fetch(:domain),
  user: "steve",
  roles: %w[app web],
  ssh_options: {
    forward_agent: true,
    auth_methods: %w[publickey]
  }
